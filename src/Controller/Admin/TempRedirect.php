<?php
/*
*
* @author Dylan Rohart
*
*/
namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TempRedirect extends AbstractController
{
    #[Route('/comming_soon', name: 'comming_soon')]
    public function smallStatsCards(): Response
    {
        return $this->render('admin/support/settings.html.twig', [
        ]);
    }
}