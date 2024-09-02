<?php
namespace App\Controller\Admin;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class LanguageController extends AbstractController
{
    #[Route('/change-language', name: 'change_language')]
    public function changeLanguage(Request $request): Response
    {
        $language = $request->request->get('language');
        $request->getSession()->set('_locale', $language);

        if ($request->isXmlHttpRequest()) {
            return new Response('Language changed to ' . $language, 200);
        } else {
            return $this->redirect($request->headers->get('referer'));
        }
    }
}
