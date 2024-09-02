<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;



class ChangeLocaleController extends AbstractController 
{
    #[Route('/change-locale/{locale}', name: 'change_locale')]
    public function changeLocale($locale, Request $request)
    {
        //Je stocke la langue dans la session
        $request->getSession()->set('_locale', $locale);
        //Redirection
        return $this->redirect($request->headers->get('referer'));
    }

    #[Route('/get-locale', name: 'get_locale', methods: ['GET'])]
    public function saveLocale(Request $request): JsonResponse
    {
        // Récupérer la langue de la session
        $locale = $request->getSession()->get('_locale', 'fr');

        // Passer la langue à la vue
        return new JsonResponse(['message' => $locale], JsonResponse::HTTP_OK);
    }
}


?>