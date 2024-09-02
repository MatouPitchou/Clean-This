<?php
namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Event\LogoutEvent;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use App\Service\LoggerService;

class LogoutEventSubscriber implements EventSubscriberInterface
{
    private $urlGenerator;
    private $loggerService;
    private $httpClient;

    public function __construct(
        UrlGeneratorInterface $urlGenerator,
        LoggerService $loggerService,
        HttpClientInterface $httpClient // Ajout du HttpClient
    ) {
        $this->urlGenerator = $urlGenerator;
        $this->loggerService = $loggerService;
        $this->httpClient = $httpClient; // Stockez le client HTTP pour utilisation
    }
    public function onLogoutEvent(LogoutEvent $event): void
    {
        $userEmail = $event->getToken()->getUser()->getEmail();

        try {
            // Envoi de la requête à Node.js pour obtenir la durée de la session
            $response = $this->httpClient->request('POST', 'http://localhost:3000/logout', [
                'json' => ['email' => $userEmail]
            ]);

            // Récupération de la durée de la session depuis la réponse
            $data = $response->toArray(); // Assurez-vous que votre API Node.js renvoie un JSON
            $sessionDuration = $data['sessionDuration'] ?? null;

            // Préparation du log
            $logData = [
                'EventTime' => date('c'),
                'loggerName' => 'uncxApp',
                'email' => $userEmail,
                'Message' => "User successfully logged out",
                'level' => 'INFO',
                'Data' => ['sessionDuration' => $sessionDuration]
            ];

            // Envoi du log à votre service de logs local ou système de gestion des logs
            $this->loggerService->sendLog($logData, 'http://localhost:3000/log');

        } catch (\Exception $e) {
            // Gérez les erreurs de requête ici
            error_log("Failed to fetch session duration: " . $e->getMessage());
        }

        // Redirection de l'utilisateur vers la page de connexion
        $response = new RedirectResponse($this->urlGenerator->generate('app_login'));
        $event->setResponse($response);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            LogoutEvent::class => 'onLogoutEvent',
        ];
    }
}
