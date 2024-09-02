<?php

/**  
 * @author Decreton Jérémy 
 */

namespace App\Services;

use App\Entity\Users;
use App\Entity\Operations;
use Doctrine\ORM\EntityManagerInterface;
use Error;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class Permissions
{

    private $tokenStorage;
    private $entityManager;

    public function __construct(TokenStorageInterface $tokenStorage, EntityManagerInterface $entityManager)
    {
        $this->tokenStorage = $tokenStorage;
        $this->entityManager = $entityManager;
    }

    public function getPermissions(Users $user): int
    {
        $permission = 0;
//Établissement des limites d'opérations en cours
        $permissionsApprenti = 1;
        $permissionsSenior = 3;
        $permissionsExpert = 5;

        $userRole = $user->getRoles();

//Test du role
        if ($userRole[0] == "ROLE_APPRENTI") {
            $permission = $permissionsApprenti;
        } elseif ($userRole[0] == "ROLE_SENIOR") {
            $permission = $permissionsSenior;
        } elseif ($userRole[0] == "ROLE_EXPERT") {
            $permission = $permissionsExpert;
        } elseif ($userRole[0] == "ROLE_ADMIN") {
            $permission = $permissionsExpert;
        } else {
            throw new \Exception("Cet utilisateur n'a pas de rôle parmis 'RÔLE_APPRENTI','RÔLE_SENIOR','RÔLE_EXPERT' ou encore 'ROLE_ADMIN'");
        }
        return $permission;
    }

    public function testPermissions(Users $user): array
    {

        $token = $this->tokenStorage->getToken();
        
        if ($token === null) {
            return [
                'success' => false,
                'permissions' => 0,
                'ongoingOperations' => 0,
            ];
        }
//Établissement des valeurs
        $ongoingOperation = 0;
        $userId = $user->getId();
        //Appel de la fonction du dessus
        $permission = $this->getPermissions($user);
        //Je récupère le repo' d'Opération pour faire ma requête
        $operationRepository = $this->entityManager->getRepository(Operations::class);
        $operationEntities = $operationRepository->findByUserId($userId);
//Test du status de l'opération
        foreach ($operationEntities as $operationEntity) {
            // Vérifiez le statut de chaque opération
            $status = $operationEntity->getStatus();
            
            //Si status est "En cours" J'augmente le compteur d'opérations "en cours" de l'utilisateur
            if ($status == "En cours") {
                $ongoingOperation++;

            }
        }
//Test entre Opération en cours et limite du rôle + redirection
        if($ongoingOperation < $permission)
        {
            return [
                'success' => true,
                'permissions' => $permission,
                'ongoingOperations' => $ongoingOperation,
            ];
        }
        elseif ($ongoingOperation >= $permission)
        {
            return [
                'success' => false,
                'permissions' => $permission,
                'ongoingOperations' => $ongoingOperation,
            ];
        }
        else
        {
            throw new \Exception("Erreur pendant le test de permission");
        }
    }

}
