<?php
/**
*
* @author: Dylan Rohart
*
*/
namespace App\EventSubscriber;

use App\Entity\Users;
use DateTimeImmutable;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityPersistedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class EasyAdminSubscriber implements EventSubscriberInterface
{
    private $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public static function getSubscribedEvents()
    {
        return [
            BeforeEntityPersistedEvent::class => ['addCreatedDateAndCustomPasswordToUsers'],
        ];
    }
 
    public function addCreatedDateAndCustomPasswordToUsers(BeforeEntityPersistedEvent $event)
    {
        $entity = $event->getEntityInstance();

        if (!($entity instanceof Users)) {
            return;
        }

        // Ajoute la date de création
        $now = new DateTimeImmutable('now');
        $entity->setCreatedAt($now);

        // Génère un mot de passe personnalisé seulement lors de la création de l'entité
        if (null === $entity->getId()) { 
            $customPassword = $this->generateCustomPassword($entity);
            $hashedPassword = $this->passwordHasher->hashPassword($entity, $customPassword);
            $entity->setPassword($hashedPassword);
        }

        $entity->setIs_verified(true);
    }

    private function generateCustomPassword(Users $user): string
    {
        $lastName = strtoupper(substr($user->getLastName(), 0, 2));
        $firstName = strtolower(substr($user->getFirstName(), 0, 2));
        $zipCode = substr($user->getZipCode(), 0, 2);

        return "{$lastName}{$firstName}@{$zipCode}";
    }
}
