<?php
/*
*
* @author Dylan Rohart
*
*/
namespace App\Controller\Employe;

use App\Entity\Users;
use App\Services\MessageGenerator;
use App\Repository\OperationsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class EmployeProfilController extends AbstractController
{
    #[Route('/employe/profil', name: 'app_employe_profil')]

    public function show(Security $security, OperationsRepository $operationsRepository, MessageGenerator $messageGenerator ): Response
    {
        $user = $security->getUser();

        $message = $messageGenerator->getMessage();

        $operations = $operationsRepository->findByUserId($user);
        return $this->render('employe/employeProfil.html.twig', [
            'user' => $user,
            'operations' => $operations,
            'message' => $message,
        ]);
    }

    
    #[Route("/employe/profil/save", name: "employe_profil_save", methods: ["POST"])]
    public function save(Request $request, Security $security, EntityManagerInterface $entityManager): Response
    {
        $data = json_decode($request->getContent(), true);
        
        $user = $security->getUser();

        if (!$user instanceof Users) {
            return $this->json(['status' => 'error', 'message' => 'Utilisateur non trouvé'], Response::HTTP_BAD_REQUEST);
        }
        
        $user->setFirstname($data['firstname']);
        $user->setLastname($data['lastname']);
        $user->setEmail($data['email']);
        $user->setPhone($data['phone']);
        
        // Persistance et sauvegarde des modifications
        $entityManager->persist($user);
        $entityManager->flush();

        // Retour d'une réponse JSON indiquant le succès de l'opération
        return $this->json(['status' => 'success']);
    }
}