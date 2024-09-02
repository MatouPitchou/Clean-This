<?php
/**
 * Structure:
 * @author: Mathilde Breux
 */
namespace App\Security;

use App\Entity\Users;
use App\Repository\UsersRepository;
use DateTime;
use League\OAuth2\Client\Provider\GoogleUser;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;

final readonly class OAuthRegistrationService 
{
    public function __construct(
        private UsersRepository $repository
    ){

    }

    /**
     * @param GoogleUser $resourceOwner
     */

    public function persist(ResourceOwnerInterface $resourceOwner): Users 
    {
        $user = (new Users())
                ->setEmail($resourceOwner->getEmail())
                ->setGoogleId($resourceOwner->getId())
                ->setRoles(['ROLE_USER'])
                ->setIs_verified(true)
                ->setActiveAt(new DateTime());

        $this->repository->add($user, true);
        return $user;
    }

}