<?php
/**
 * Structure:
 * @author: Mathilde Breux
 */
namespace App\EventListener;

use DateTime;
use Exception;
use App\Entity\Users;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\SecurityEvents;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Core\Event\AuthenticationSuccessEvent;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class UserLoginListener
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        $user = $event->getAuthenticationToken()->getUser();
        if ($user instanceof Users) {
            // Mettre à jour la date de dernière connexion
            $user->setActiveAt(new DateTime());
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }
    }
    public function onAuthenticationSuccess(AuthenticationSuccessEvent $event)
    {
        $user = $event->getAuthenticationToken()->getUser();
        if ($user instanceof Users) {
            // Mettre à jour la date de dernière connexion
            $user->setActiveAt(new DateTime());
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }
    }
}