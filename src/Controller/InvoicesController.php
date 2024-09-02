<?php

namespace App\Controller;

use App\Entity\Invoices;
use App\Services\SendMail;
use App\Services\InvoiceGenerator;
use Symfony\Component\Mime\Address;
use App\Repository\OperationsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;

// #[Route('/invoices')]
class InvoicesController extends AbstractController
{

    private $entityManager;
    private $invoiceGenerator;
    private String $pdfDirectory;


    public function __construct(EntityManagerInterface $entityManager, InvoiceGenerator $invoiceGenerator, String $pdfDirectory)
    {
        $this->entityManager = $entityManager;
        $this->invoiceGenerator = $invoiceGenerator;
        $this->pdfDirectory = $pdfDirectory;
    }

    #[Route('/invoice/new', name: 'createInvoice', methods: ['GET', 'POST'])]
    public function new(TranslatorInterface $translator, SendMail $sendMail, OperationsRepository $operationsRepository, EntityManagerInterface $entityManager, Request $request): JsonResponse
    {
        try {
            $parameters = json_decode($request->getContent(), true);
            $operationId = $parameters['operationId'];
            // Rechercher l'opération correspondante dans la base de données
            $operation = $operationsRepository->find($operationId);

            // Vérifie si une facture est déjà associée à cette opération
            if ($operation->getInvoices()) {
                return new JsonResponse(['error' => "Une facture a déjà été créée pour cette opération"], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
            }

            $users = $operation->getUsers();
            $createdAt = $operation->getCreatedAt();
            $operationCreatedAt = $createdAt->format('d-m-Y H:i');

            // Parcourir les utilisateurs pour trouver celui dont le rôle est "User"
            foreach ($users as $currentUser) {
                if ($currentUser->getRoles() != 'ROLE_APPRENTI' && $currentUser->getRoles() != 'ROLE_EXPERT' && $currentUser->getRoles() != 'ROLE_SENIOR') {
                    $user = $currentUser;
                }
            }

            if ($user === null) {
                // Gérer le cas où aucun utilisateur avec le rôle "User" n'a été trouvé
                return new Response('No user with role "User" associated with this operation', Response::HTTP_NOT_FOUND);
            }

            // Utilisez le service InvoiceGenerator pour générer la facture
            $pdf = $this->invoiceGenerator->generateInvoice(
                $user->getFirstname(),
                $user->getLastname(),
                $user->getPhone(),
                $user->getStreet(),
                $user->getCity(),
                $user->getZipcode(),
                $operation->getServices()->getType(),
                $operation->getPrice(),
                $operation->getDescription(),
                $operationCreatedAt,
                $operationId,
            );

            $pdfUrl = $this->invoiceGenerator->generatePdfUrl($operationId);

            // Créez une nouvelle instance de l'entité Invoices
            $invoice = new Invoices();

            // Définissez les données de la facture
            $invoice->setPaid(false);
            $invoice->setCreatedAt(new \DateTimeImmutable());
            // Enregistrez l'URL complète du PDF dans la base de données
            $invoice->setPath($pdfUrl);

            // Associez la facture à l'opération
            $operation->setInvoices($invoice);

            // Persistez et flush la facture
            $entityManager->persist($invoice);
            $entityManager->flush();

            //envoi de la facture au client par mail
            $from = (new Address('noreply@cleanthis.com', 'Clean This'));
            $to = $user->getEmail();
            $subject =  $translator->trans('your_invoice');
            $template = 'invoiceEmail';
            $context = [
                'firstname' => $user->getFirstname(),
            ];

            // Envoyer la facture par email
            $sendMail->sendWithAttachment($from, $to, $subject, $template, $context, $pdf);

            return new JsonResponse(['message' => 'Facture générée avec succès'], JsonResponse::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/invoice/{operationId}', name: 'showInvoice', methods: ['GET'])]
    public function show(TranslatorInterface $translator, OperationsRepository $operationsRepository, int $operationId, Security $security): BinaryFileResponse
    {
        // Rechercher l'opération correspondante dans la base de données
        $operation = $operationsRepository->find($operationId);

        if (!$operation) {
            throw $this->createNotFoundException('Operation not found for ID: ' . $operationId);
        }

        // Vérifier si l'utilisateur est autorisé à accéder à cette opération
        if (!$this->isUserAuthorized($operation, $security)) {
            throw $this->createAccessDeniedException('You are not authorized to access this invoice.');
        }

        $invoice = $operation->getInvoices();

        if (!$invoice) {
            throw $this->createNotFoundException('Invoice not found for operation ID: ' . $operationId);
            $message = $translator->trans('invoiceNotCreated');
            $this->addFlash('error', $message);
        }

        $pdfPath = $invoice->getPath();

        if (!$pdfPath) {
            throw $this->createNotFoundException('PDF path not found for invoice with operation ID: ' . $operationId);
        }

        // Return the PDF file as BinaryFileResponse
        return new BinaryFileResponse($pdfPath);
    }

    #[Route('/invoice/{operationId}/update', name: 'updateInvoice', methods: ['GET', 'POST'])]
    public function update(TranslatorInterface $translator, Security $security, OperationsRepository $operationsRepository, int $operationId, Request $request, EntityManagerInterface $entityManager): RedirectResponse
    {
        // Rechercher l'opération correspondante dans la base de données
        $operation = $operationsRepository->find($operationId);

        if (!$operation) {
            throw $this->createNotFoundException('Operation not found for ID: ' . $operationId);
        }

        // Vérifier si l'utilisateur est autorisé à accéder à cette opération
        if (!$this->isUserAuthorized($operation, $security)) {
            throw $this->createAccessDeniedException('You are not authorized to access this invoice.');
        }

        $invoice = $operation->getInvoices();

        if (!$invoice) {
            throw $this->createNotFoundException('Invoice not found for operation ID: ' . $operationId);
        }

        if ($invoice->isPaid() == true) {
            $message = $translator->trans('invoiceAlreadyPaid');
            $this->addFlash('warning',$message);
        } elseif ($invoice->isPaid() != true) {
            $invoice->setPaid(true);
            $invoice->setPaidAt(new \DateTimeImmutable());
            // Persistez et flush la facture
            $entityManager->persist($invoice);
            $entityManager->flush();
            $message = $translator->trans('invoicePaid');
            $this->addFlash('success', $message);
        };

        $referer = $request->headers->get('referer');
        return new RedirectResponse($referer);
    }

    private function isUserAuthorized($operation, Security $security): bool
    {
        // Récupérer l'utilisateur actuel
        $user = $security->getUser();

        // Vérifier si l'utilisateur est lié à l'opération
        // Et s'il a le rôle "ROLE_ADMIN"
        if ($operation->getUsers()->contains($user) || $this->isGranted('ROLE_ADMIN')) {
            return true;
        }
        return false;
    }
}
