<?php
/**
 * Structure:
 * @author: Amélie
 */

 namespace App\EventListener;

use App\Entity\Operations;
use App\Service\LoggerService;
use Symfony\Bundle\SecurityBundle\Security;
use Doctrine\Persistence\Event\LifecycleEventArgs;

class OperationStatusListener
{
    private $security;
    private LoggerService $loggerService;


    public function __construct(Security $security, LoggerService $loggerService)
    {
        $this->loggerService = $loggerService;
        $this->security = $security;

    }

    public function postUpdate(LifecycleEventArgs $args)
    {

        $operation = $args->getObject();

        // Check if the status changed to "Terminée"
        if ($operation instanceof Operations &&  $operation->getStatus() === 'Terminée') {
            $user = $this->security->getUser();
            $userData = [
                'EventTime' => date('c'),
                'loggerName' => 'finishedOP',
                'email' => $user->getEmail(),
                'Message' => "Finished operation retrieved",
                'level' => 'INFO',
                'data' => [
                    'userName' => $user->getFirstname(), 
                    'userRole' => $user->getRoles(),
                    'userCreationDate' => $user-> getCreatedAt(), 
                    'operationCreatedAt' => $operation->getCreatedAt(),
                    'operationFinishedAt' => $operation->getFinishedAt()
                    ]
            ];
        
            $this->loggerService->sendLog($userData, 'http://localhost:3000/log');
            
        }
    }
}
