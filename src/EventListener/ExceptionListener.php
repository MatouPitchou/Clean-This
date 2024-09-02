<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Twig\Environment;

class ExceptionListener
{
    private $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    public function onKernelException(ExceptionEvent $event)
    {
        // Récupère l'exception
        $exception = $event->getThrowable();

        // Vérifie si c'est une exception NotFoundHttpException pour les erreurs 404
        if ($exception instanceof HttpExceptionInterface && $exception->getStatusCode() == Response::HTTP_NOT_FOUND) {
            // Génère la réponse avec le template personnalisé
            $response = new Response();
            $response->setContent($this->twig->render('bundles/TwigBundle/Exception/error404.html.twig'));

            // Définit la réponse dans l'événement, ce qui arrête la propagation et affiche la page d'erreur personnalisée
            $event->setResponse($response);
        }
    }
}
