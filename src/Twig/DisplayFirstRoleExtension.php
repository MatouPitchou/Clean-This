<?php
// Affichage du rôle de l'utilisateur dans une div
/*
*
* @author: Dylan Rohart
*
*/
namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Symfony\Component\Security\Core\Security;

class DisplayFirstRoleExtension extends AbstractExtension
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('get_user_role_label', [$this, 'getUserRoleLabel']),
        ];
    }

    public function getUserRoleLabel()
    {
        $user = $this->security->getUser();
        if (!$user) {
            return 'Non connecté';
        }

        $role = $user->getRoles()[0]; // Supposons au moins un rôle

        switch ($role) {
            case 'ROLE_ADMIN':
                return 'Expert';
            case 'ROLE_SENIOR':
                return 'Sénior';
            case 'ROLE_APPRENTI':
                return 'Apprenti';
            default:
                return 'Non défini';
        }
    }
}
