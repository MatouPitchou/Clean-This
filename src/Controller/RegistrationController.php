<?php

/**
 * Structure:
 * @author: Mathilde Breux
 */

namespace App\Controller;

use App\Entity\Users;
use App\Services\SendMail;
use App\Services\JWTService;
use App\Form\RegistrationFormType;
use App\Security\AppAuthenticator;
use App\Repository\UsersRepository;
use App\Service\LoggerService;
use Symfony\Component\Mime\Address;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;

class RegistrationController extends AbstractController
{
    private LoggerService $loggerService;

    public function __construct(LoggerService $loggerService)
    {  
        $this->loggerService = $loggerService;
    }


    #[Route('/register', name: 'app_register')]
    public function register(TranslatorInterface $translator, Request $request, UserPasswordHasherInterface $userPasswordHasher, UserAuthenticatorInterface $userAuthenticator, AppAuthenticator $authenticator, EntityManagerInterface $entityManager, SendMail $mail, JWTService $jwt): Response
    {
        $user = $this->getUser();
        // Deny access the user isn't connected
        if ($user) {
            $this->addFlash('warning', "Vous êtes déjà inscrit à notre site");
            return $this->redirectToRoute('home');
        }

        $user = new Users();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager->persist($user);
            $entityManager->flush();

            //On génère le JWT de l'utilisateur
            //header
            $header = [
                'alg' => 'HS256',
                'typ' => 'JWT'
            ];

            //payload
            $payload = [
                'user_id' => $user->getId()
            ];

            //token
            $token = $jwt->generate($header, $payload, $this->getParameter('app.jwtsecret'));

            $from = (new Address('noreply@cleanthis.com', 'Clean This'));
            $to = $user->getEmail();
            $subject =  $translator->trans('account_activation');
            $template = 'register';
            $context = [
                'token' => $token,
                'firstname' => $user->getFirstname()
            ];

            $mail->send($from, $to, $subject, $template, $context);

            $email = $user->getEmail(); // Récupérer l'email de l'utilisateur

            $userData = [
                'EventTime' => date('c'),
                'loggerName' => 'rgstApp',
                'email' => $email, // Utiliser l'email récupéré
                'Message' => "User successfully registered!",
                'level' => 'INFO',
            ];
        
            $this->loggerService->sendLog($userData, 'http://localhost:3000/log');
    
            

            return $userAuthenticator->authenticateUser(
                $user,
                $authenticator,
                $request
            );
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }
    #[Route('/verif/{token}', name: 'verify_user')]
    public function verifyUser($token, JWTService $jwt, UsersRepository $userRepository, EntityManagerInterface $em): Response
    {

        // On vérifie si le token est valide, n'a pas expiré et n'a pas été modifié
        if ($jwt->isValid($token) && !$jwt->isExpired($token) && $jwt->check($token, $this->getParameter('app.jwtsecret'))) {

            // on récupère le payload
            $payload = $jwt->getPayload($token);

            //on récupère le user du token
            $user = $userRepository->find($payload['user_id']);

            //on vérifie que l'utilisateur existe et n'a pas encore activé son compte
            if ($user && !$user->getIs_verified()) {
                $user->setIs_verified(true);
                $em->flush($user);
                $this->addFlash('success', 'Utilisateur vérifié');
                return $this->redirectToRoute('home');
            }
        }
        //sinon, en cas de problème
        $this->addFlash('danger', 'le Token est invalide ou a expiré');
        return $this->redirectToRoute('app_login');
    }

    #[Route('/renvoiverif', name: 'resend_verif')]
    public function resendVerif(TranslatorInterface $translator, JWTService $jwt, SendMail $mail, UsersRepository $userRepository): Response
    {
        $user = $this->getUser();
        /** @var \App\Entity\Users $user 
         * do not remove this comment
         */

        if (!$user) {
            $this->addFlash('danger', 'Vous devez être connecté pour accéder à cette page');
            return $this->redirectToRoute('app_login');
        }

        if ($user->getIs_verified()) {
            $this->addFlash('warning', 'Cet utilisateur est déjà activé');
            return $this->redirectToRoute('home');
        }
        //On génère le JWT de l'utilisateur
        //header
        $header = [
            'alg' => 'HS256',
            'typ' => 'JWT'
        ];

        //payload
        $payload = [
            'user_id' => $user->getId()
        ];

        //token
        $token = $jwt->generate($header, $payload, $this->getParameter('app.jwtsecret'));



        $from = (new Address('noreply@cleanthis.com', 'Clean This'));
        $to = $user->getEmail();
        $subject =  $translator->trans('account_activation');
        $template = 'register';
        $context = [
            'token' => $token,
            'firstname' => $user->getFirstname()
        ];

        $mail->send($from, $to, $subject, $template, $context);
        $this->addFlash('success', 'E-Mail envoyé');
        return $this->redirectToRoute('home');
    }
}
