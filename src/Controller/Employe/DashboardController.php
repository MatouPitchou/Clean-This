<?php

namespace App\Controller\Employe;

use App\Entity\Operations;
use App\Repository\OperationsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;

class DashboardController extends AbstractDashboardController
{

/*
 *
 *  @author: Mathilde Brx
 * 
 */


    private $entityManager;

    // Modifiez le constructeur pour injecter EntityManagerInterface
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/employe/data', name: 'employe_data')]
    public function data(Request $request, TranslatorInterface $translator): JsonResponse {
        // SWITCH ALLTIME TO MENSUEL ET INVERSEMENT 

        $type = $request->query->get('type', 'alltime'); // 'alltime' par défaut
    
        if ($type === 'mensuel') {
            //  récupérer les données du mois actuel
            $operationsData = $this->entityManager->getRepository(Operations::class)->getMonthlyOperationsData();
        } else {
            //  récupérer les données all time (de base)
            $operationsData = $this->entityManager->getRepository(Operations::class)->getOperationsData();
        }
    
        $labels = [];
        $data = [];
        foreach ($operationsData as $operation) {
            $labels[] = $operation['typeLabel'];
            $data[] = $operation['operationsCount'];
        }

        $translatedLabels = [
            'À estimer' => $translator->trans('badge_estime_label', [], 'messages'),
            'Petite' => $translator->trans('badge_petite_label', [], 'messages'),
            'Moyenne' => $translator->trans('badge_moyenne_label', [], 'messages'),
            'Grande' => $translator->trans('badge_grande_label', [], 'messages'),
        ];

        $translatedAverageLabels = [
            'Petite' => $translator->trans('badge_petite_label', [], 'messages'),
            'Moyenne' => $translator->trans('badge_moyenne_label', [], 'messages'),
            'Grande' => $translator->trans('badge_grande_label', [], 'messages'),
            'Custom' => 'Custom',
        ];
    
        return new JsonResponse([
            'operationsLabels' => $labels,
            'operationsCounts' => $data,
            'translatedLabels' => $translatedLabels,
            'translatedAverageLabels' => $translatedAverageLabels,
            'averageOpenedTimeLabel' => $translator->trans('average_opened_time', [], 'messages'),
        ]);
    }
    



    #[Route('/employe', name: 'employe')]
    public function index(): Response {
        // Stats des petites cartes du haut de page
        $ongoingOrder = $this->entityManager->getRepository(Operations::class)->ongoingOrder();
        $mostOrdered = $this->entityManager->getRepository(Operations::class)->mostOrdered();
        $finishedOrder = $this->entityManager->getRepository(Operations::class)->finishedOrder();
        $finishedOrderDifference = $this->entityManager->getRepository(Operations::class)->findEndedOperationDifferences();
        // Graphique Donnut - Nombre de services commandé par type
        $operationsData = $this->entityManager->getRepository(Operations::class)->getOperationsData();
        $numberOfOperations = $this->entityManager->getRepository(Operations::class)->count();
        $monthlyOperationsDifference = $this->entityManager->getRepository(Operations::class)->getMonthlyOperationsDifference();
        $monthlyOperationsPercent = $this->entityManager->getRepository(Operations::class)->getMonthlyOperationsPercentageDifference();
        // Graphiques lines - Revenue par employé par mois
        $chartData = $this->entityManager->getRepository(Operations::class)->calculateRevenueByEmployeeAndMonth();
        $totalCA = $this->entityManager->getRepository(Operations::class)->calculateAllRevenues();
        $compareAnnualRevenues = $this->entityManager->getRepository(Operations::class)->compareAnnualRevenues();
        // Graphiques Bars - Temps moyen de fermeture d'une opérations
        $averageCompletionHoursByServiceType = $this->entityManager->getRepository(Operations::class)->getAverageCompletionHoursByServiceType();

        //vue de l'historique générale 
        $latestOperations = $this->entityManager->getRepository(Operations::class)->findBy([], ['id' => 'DESC'], 5);


        $averageLabels = [];
        $averageData = [];
        $labels = [];
        $data = [];
        foreach ($operationsData as $operation) {
            $labels[] = $operation['typeLabel'];
            $data[] = $operation['operationsCount'];
        }

        foreach ($averageCompletionHoursByServiceType as $average) {
            $averageLabels[] = $average['Type'];
            $averageData[] = $average['AverageCompletionHours'];
        }
        
    
        return $this->render('employe/dashboard.html.twig', [
            'operationsData' => $operationsData, 
            'operationsLabels' => $labels,
            'operationsCounts' => $data, 
            'numberOfOperations' => $numberOfOperations,
            'monthlyOperationsDifference' => $monthlyOperationsDifference,
            'monthlyOperationsPercent' => $monthlyOperationsPercent,
            'chartData' => $chartData,
            'totalCA' => $totalCA,
            'ongoingOrder' => $ongoingOrder,
            'mostOrdered' => $mostOrdered,
            'finishedOrder' => $finishedOrder,
            'finishedOrderDifference' => $finishedOrderDifference,
            'compareAnnualRevenues' => $compareAnnualRevenues,
            'averageLabels' => $averageLabels, 
            'averageData' => $averageData,
            'latestOperations' => $latestOperations
        ]);
    }
/**
 *  @author Jérémy <jeremydecreton@live.fr>
 * 
 */


    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
        ->setTitle('<img src="assets/black_logo.svg" style="width:30px; height: auto; float: left;"> Clean This')
        ->setFaviconPath('assets/black_logo.svg')
        ->disableDarkMode();
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::section('GENERAL');
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-list');
        yield MenuItem::linkToCrud('order_label', 'fas fa-basket-shopping', Operations::class)
        ->setController(CommandsCrudController::class);
        
        
        yield MenuItem::section('title_menu_tools_label'); 
        yield MenuItem::linkToCrud('personal_hystory_label', 'fa fa-business-time', Operations::class)
        ->setController(OperationsCrudController::class); 
        yield MenuItem::linkToRoute('profil_title_label', 'fa fa-address-card', 'app_employe_profil');
        

        yield MenuItem::section('SUPPORT');
        yield MenuItem::linkToRoute('settings_label', 'fas fa-gear', 'comming_soon');
        yield MenuItem::linkToRoute('help_label', 'fas fa-circle-question', 'comming_soon');
    }

    
}
