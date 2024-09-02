<?php

/**
 * Chiefs of operations:
 * @author: Jérémy Decreton, Amélie Gattepaille
 */

namespace App\Controller;

use App\Entity\Users;
use App\Entity\Operations;
use App\Form\OperationsType;
use App\Service\LoggerService;
use App\Repository\OperationsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/operations')]
class OperationsController extends AbstractController
{

    private LoggerService $loggerService;

    public function __construct(LoggerService $loggerService)
    {  
        $this->loggerService = $loggerService;
    }

    
    #[Route('/', name: 'app_operations_index', methods: ['GET'])]
    public function index(TranslatorInterface $translator, OperationsRepository $operationsRepository): Response
    {

        /** @var \App\Entity\Users $user 
         * do not remove this comment
         */
        $user = $this->getUser();
        // Deny connection to DB if the user isn't connected
        if (!$user) {
            $message = $translator->trans('loginRequired');
            $this->addFlash('error', $message);
            return $this->redirectToRoute('home');
        }

        $operations = $operationsRepository->findByUserId($user);
        $ongoingOperationsClient = $operationsRepository->onGoingOperationsClient($user);
        $mostWantedService = $operationsRepository->mostWantedService($user->getId());
        $finishedOperationsClient = $operationsRepository->finishedOperationsClient($user);
        $numberPerServiceonGoing = $operationsRepository->getNumberPerService($user->getId(), "En cours", "Disponible");
        $numberPerServiceFinished = $operationsRepository->getNumberPerService($user->getId(), "Terminée", "");


        // empêche l'accès aux employés et à l'admin
        if ($this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_SENIOR') || $this->isGranted('ROLE_APPRENTI')) {
            $message = $translator->trans('acessDenied');
            $this->addFlash('error', $message);
            return $this->redirectToRoute('home');
        }

        return $this->render('operations/index.html.twig', [
            'operations' => $operations,
            'ongoingOperationsClient' => $ongoingOperationsClient,
            'mostWantedService' => $mostWantedService,
            'finishedOperationsClient' => $finishedOperationsClient,
            'numberPerServiceonGoing' => $numberPerServiceonGoing,
            'numberPerServiceFinished' => $numberPerServiceFinished
        ]);
    }
    
   #[Route('/new', name: 'app_operations_new', methods: ['GET', 'POST'])]
    public function new(TranslatorInterface $translator, Request $request, EntityManagerInterface $entityManager, Security $security): Response
    {

        // Get the connected User ID
        /** @var \App\Entity\Users $user 
         * do not remove this comment */
         
        $user = $this->getUser();

        // Deny access the user isn't connected
        if (!$user) {
            $message = $translator->trans('registerToOrder');
            $this->addFlash('error', $message);
            return $this->redirectToRoute('app_login');
        }

        // empêche l'accès aux employés et à l'admin
        if ($this->isGranted('ROLE_APPRENTI')) {
            $message = $translator->trans('acessDenied');
            $this->addFlash('error', $message);
            return $this->redirectToRoute('home');
        }

        // Create a new operation object
        $operation = new Operations();
        // Give the user object to the operation object
        $operation->addUserId($user);

        $form = $this->createForm(OperationsType::class, $operation, [
            'current_user' => $user,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Deny permission if the user isn't verified
           if (!$user->getIs_verified()) {
            $message = $translator->trans('verifyToOrder');
            return new JsonResponse(['status' => 'error', 'message' => $message]);                
            } 

            //Si l'utilisateur s'est inscrit avec google, il doit remplir ses infos perso avant de commander.
        if ($user->getGoogleId() != NULL && (!$user->getFirstName() || !$user->getLastName()  || !$user->getZipcode() || !$user->getCity())) {
              $message = $translator->trans('fillProfileToOrder');
            return new JsonResponse(['status' => 'error', 'message' => $message]);

            }


            $entityManager->persist($operation);
            $entityManager->flush();
            $message = $translator->trans('orderSuccess');

            $email = $user->getEmail(); // Récupérer l'email de l'utilisateur
            $city = $operation->getCity();
            $zipCode = $operation->getZipcode();
            $street = $operation->getStreet();
            $desc = $operation->getDescription();

            $userData = [
                'EventTime' => date('c'),
                'loggerName' => 'crtOp',
                'email' => $email, // Utiliser l'email récupéré
                'Message' => "An operation has been created !",
                'OpDesc' => $desc,
                'OpAdress' => $street." ".$zipCode." ".$city,
                'level' => 'INFO',
            ];
        
            $this->loggerService->sendLog($userData, 'http://localhost:3000/log');

            return new JsonResponse(['status' => 'success', 'message' =>  $message]);

        }

        return $this->render('operations/new.html.twig', [
            'operation' => $operation,
            'form' => $form,
            'user' => $user,
        ]);
    } 

    #[Route('/{id}', name: 'app_operations_show', methods: ['GET'])]
    public function show(TranslatorInterface $translator, Operations $operation): Response
    {

        // Get the connected User ID
        $user = $this->getUser();

        // Deny connection to DB if the user isn't connected
        if (!$user) {
            $message = $translator->trans('loginRequired');
            $this->addFlash('error', $message);
            return $this->redirectToRoute('home');
        }

        // Deny access if the user is not related to the operation
        if (!$operation->getUsers()->contains($user)) {
            $message = $translator->trans('acessDenied');
            $this->addFlash('error', $message);
            return $this->redirectToRoute('home');
        }

        // deny acces to employees and the admin
        if ($this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_SENIOR') || $this->isGranted('ROLE_APPRENTI')) {
            $message = $translator->trans('acessDenied');
            $this->addFlash('error', $message);
            return $this->redirectToRoute('home');
        }

        $users = $operation->getUsers();

        /* if ($operation->getInvoices()) {
            $invoice = $operation->getInvoices();
        } */
        return $this->render('operations/show.html.twig', [
            'operation' => $operation,
            'users' => $users,
            // 'invoice' => $invoice
        ]);
    }

    #[Route('/{id}/edit', name: 'app_operations_edit', methods: ['PUT'])]
    public function edit(TranslatorInterface $translator, Request $request, Operations $operation, EntityManagerInterface $entityManager): JsonResponse
    {
        // Get the connected User ID
        $user = $this->getUser();

        // Deny connection to DB if the user isn't connected
        if (!$user) {
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

        if ($user) {

            // Vérifie si la requête est une requête Ajax
            if ($request->isXmlHttpRequest()) {
                // Récupère les données JSON de la requête
                $data = json_decode($request->getContent(), true);

                // Vérifie si la clé 'quote' est présente dans les données
                if (isset($data['quote']) && isset($data['status'])) {
                    // Met à jour la valeur de 'quote' de l'opération
                    $newQuote = $data['quote'];
                    $newStatus = $data['status'];
                    $operation->setQuote($newQuote);
                    $operation->setStatus($newStatus);

                    // Enregistre les modifications dans la base de données
                    $entityManager->flush();

                    // Répond avec un statut de succès
                    return new JsonResponse(['status' => 'success']);
                } else {
                    // Répond avec un statut d'erreur si la clé 'quote' est manquante
                    return new JsonResponse(['status' => 'error', 'message' => 'Quote data not provided']);
                }
            } else {
                // Répond avec un statut d'erreur si la requête n'est pas une requête Ajax
                return new JsonResponse(['status' => 'error', 'message' => 'This route only accepts Ajax requests']);
            }
        }
    }

    #[Route('/{id}', name: 'app_operations_delete', methods: ['DELETE'])]
    public function delete(TranslatorInterface $translator, Security $security, Operations $operation, EntityManagerInterface $entityManager): Response
    {
        $currentUser = $security->getUser();
        if (!$operation->getUsers()->contains($currentUser)) {
            // L'utilisateur n'est pas autorisé à supprimer cette opération
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à supprimer cette opération.');
        }

        // Deny connection to DB if the user isn't connected
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

        // Supprimer l'opération
        $entityManager->remove($operation);
        $entityManager->flush();

        // Répondre avec un statut de succès
        $response = ['status' => 'success'];
        return new JsonResponse($response);
    }
}
