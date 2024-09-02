<?php

/**
 * Structure:
 * @author: Mathilde Breux
 */

namespace App\Controller;

use App\Entity\Users;
use App\Form\UserType;
use App\Services\MessageGenerator;
use App\Form\ChangeUserPasswordType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ProfilController extends AbstractController
{
    #[Route('/profil', name: 'app_profil', methods: ['GET'])]
    public function show(TranslatorInterface $translator, Security $security, Users $user, MessageGenerator $messageGenerator,  Request $request): Response
    {
        $currentUser = $security->getUser();
        $locale = $request->getLocale();

        if (!$currentUser) {
            $message = $translator->trans('loginRequired');
            $this->addFlash('error', $message);
            return $this->redirectToRoute('home');
        }

        if ($this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_SENIOR') || $this->isGranted('ROLE_APPRENTI')) {
            $message = $translator->trans('acessDenied');
            $this->addFlash('error', $message);
            return $this->redirectToRoute('home');
        }

        // Générer le message du profil en fonction de la langue
        if ($locale === 'fr') {
            $message = $messageGenerator->getJoyeuxMessage();
        } elseif ($locale === 'en') {
            $message = $messageGenerator->getHappyMessage();
        }

        return $this->render('profil/index.html.twig', [
            'user' => $currentUser,
            'message' => $message,
        ]);
    }

    //Pour éditer le profil
    #[Route('/profil/{id}/edit', name: 'app_profil_edit', methods: ['GET', 'POST'])]
    public function edit(TranslatorInterface $translator, Request $request, Security $security, Users $user, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        $currentUser = $security->getUser();

        // Vérifier si l'utilisateur est bien connecté
        if (!$currentUser) {
            $message = $translator->trans('loginRequired');
            $this->addFlash('error',  $message);
            return $this->redirectToRoute('home');
        }

        //Si l'utilisateur veut modifier un autre profil que le sien à l'aide d'un ID dans l'url, redirection vers profil.
        if ($currentUser !== $user) {
            return $this->redirectToRoute('app_profil');
        }

        // empêche l'accès aux employés et à l'admin
        if ($this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_SENIOR') || $this->isGranted('ROLE_APPRENTI')) {
            $message = $translator->trans('acessDenied');
            $this->addFlash('error', $message);
            return $this->redirectToRoute('home');
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $res = array('status' => true);
            $entityManager->flush();
            $message = $translator->trans('profilInformationsModified');
            $this->addFlash('success', $message);

            return $this->redirectToRoute('app_profil', [], Response::HTTP_SEE_OTHER);
        } else {
            $res = array('status' => false);
        }
        if ($request->isXmlHttpRequest()) {
            return $this->json($res);
        }

        return $this->render('profil/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/profil/{id}/editPassword', name: 'app_profil_editPassword', methods: ['GET', 'POST'])]
    public function editPassword(TranslatorInterface $translator, Users $user, Security $security, Request $request, EntityManagerInterface $manager, UserPasswordHasherInterface $hasher): Response
    {
        $form = $this->createForm(ChangeUserPasswordType::class);

        $form->handleRequest($request);

        //Si l'utilisateur s'est inscrit avec google, on ne veut pas qu'il modifie son mot de passe car il n'en a pas donc redirection vers profil.
        if ($user->getGoogleId() !== NULL) {
            return $this->redirectToRoute('app_profil');
        }
        $currentUser = $security->getUser();
        // Vérifier si l'utilisateur est bien connecté
        if (!$currentUser) {
            $message = $translator->trans('loginRequired');
            $this->addFlash('error', $message);
            return $this->redirectToRoute('home');
        }

        //Si l'utilisateur veut modifier un autre profil que le sien à l'aide d'un ID dans l'url, redirection vers profil.
        if ($this->getUser() !== $user) {
            return $this->redirectToRoute('app_profil');
        }
        if ($form->isSubmitted() && $form->isValid()) {
            if ($hasher->isPasswordValid($user, $form->getData()['plainPassword'])) {
                $user->setPassword(
                    $hasher->hashPassword(
                        $user,
                        $form->getData()['newPassword']
                    )
                );
                $message = $translator->trans('passwordModified');
                $this->addFlash(
                    'success',$message
                );

                $manager->persist($user);
                $manager->flush();

                return $this->redirectToRoute('app_profil');
            } else {
                $message = $translator->trans('wrongPassword');
                $this->addFlash(
                    'warning',$message
                );
            }
        }
        return $this->render('profil/editPassword.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('profil/{id}/delete', name: 'app_profil_delete', methods: ['POST'])]
    public function delete(TranslatorInterface $translator, Request $request, Security $security, Users $user, EntityManagerInterface $entityManager,): Response
    {
        $currentUser = $security->getUser();

        // Vérifier si l'utilisateur est bien connecté
        if (!$currentUser) {
            $message = $translator->trans('loginRequired');
            $this->addFlash('error', $message);
            return $this->redirectToRoute('home');
        }

        // empêche l'accès aux employés et à l'admin
        if ($this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_SENIOR') || $this->isGranted('ROLE_APPRENTI')) {
            $message = $translator->trans('acessDenied');
            $this->addFlash('error', $message);
            return $this->redirectToRoute('home');
        }

        if ($this->isCsrfTokenValid('delete' . $user->getId(), $request->request->get('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
            $request->getSession()->invalidate();
            $this->container->get('security.token_storage')->setToken(null);
            $message = $translator->trans('accountDeleted');
            $this->addFlash('success',$message);
            return $this->redirectToRoute('home', [], Response::HTTP_SEE_OTHER);
        }
        return $this->redirectToRoute('home');
    }
}
