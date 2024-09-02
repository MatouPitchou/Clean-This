<?php

namespace App\Controller;

use App\Repository\OperationsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SmallStatsCardsController extends AbstractController
{

    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    #[Route('/admin/smallstatscards', name: 'app_small_stats_cards')]
    public function smallStatsCards(): Response
    {

         // Vérifier si l'utilisateur est authentifié et a le rôle d'administrateur
    /*      if ($this->security->isGranted('ROLE_ADMIN')) {
            // Rediriger vers la page d'administration ou une autre page appropriée
            return $this->redirectToRoute('admin'); // Remplacez 'admin_login' par le nom de la route de votre page de connexion admin
        } */

        return $this->render('_partials/_smallStatsCards.html.twig', [
        ]);
    }

    #[Route('/admin/graphfirstsection', name: 'app_graph_first_section')]
    public function GraphFirstSection(): Response
    {

          // Vérifier si l'utilisateur est authentifié et a le rôle d'administrateur
      /*     if ($this->security->isGranted('ROLE_ADMIN')) {
            // Rediriger vers la page d'administration ou une autre page appropriée
            return $this->redirectToRoute('admin'); // Remplacez 'admin_login' par le nom de la route de votre page de connexion admin
        } */
        
        return $this->render('_partials/_GraphFirstSection.html.twig', [
        ]);
    }
    
}
