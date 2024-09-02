<?php

/**
 *  @author Jérémy <jeremydecreton@live.fr>
 *
 */

namespace App\Controller\Employe;

use App\Entity\Users;
use App\Entity\Services;
use App\Entity\Operations;
use App\Services\Permissions;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use Symfony\Component\HttpFoundation\RedirectResponse;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class CommandsCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Operations::class;
    }


    private $entityManager;
    private $request;
    private $requestStack;


    public function __construct(EntityManagerInterface $entityManager, RequestStack $requestStack)
    {
        $this->entityManager = $entityManager;
        $this->request = $requestStack->getCurrentRequest();
        $this->requestStack = $requestStack;
    }

    //Établissement des fonctions propre au contrôleur
    public function getUserID()
    {
        $user = $this->getUser();

        if ($user instanceof Users) {
            $userId = $user->getId();
            return $userId;
        }
    }
    //Fin

    //Configuration du CRUD, Actions & Fields
    public function configureCrud(Crud $crud): Crud
    {
       return $crud
       ->setEntityLabelInPlural('order_label')
       ->setEntityLabelInSingular('order_label')
       ->overrideTemplate('crud/index', 'admin/pop-up/attribute.html.twig')
       ->setHelp('index','order_info_label')
       ->setPageTitle('index', 'order_label')
       ->setPageTitle('edit', "Estimation")
       ;
    }


    public function configureActions(Actions $actions): Actions
    {
        $takeOperation = Action::new('takeOperation', 'take_label', "fas fa-angle-double-down" )
        ->linkToCrudAction('takeOperation')
        ->displayAsLink();
        $assignOperationTo = Action::new('assignOperationTo','attributing_label', "fas fa-user-plus" )
        ->linkToCrudAction('assignOperationTo')
        ->displayAsLink();

        return $actions
        ->remove(Crud::PAGE_INDEX, Action::NEW)
        ->remove(Crud::PAGE_INDEX, Action::DELETE)
        ->remove(Crud::PAGE_EDIT, Action::SAVE_AND_CONTINUE)
        ->add(Crud::PAGE_INDEX, $takeOperation)
        ->add(Crud::PAGE_INDEX, $assignOperationTo)
        ->setPermission($assignOperationTo, 'ROLE_ADMIN')
        ->update(Crud::PAGE_EDIT, Action::SAVE_AND_RETURN, function (Action $saveReturn){
            return $saveReturn->setLabel("estimate_label");
        })
        ->update(Crud::PAGE_INDEX, Action::EDIT, function (Action $action)
        {
            $action->linkToRoute("redirect_or_deny_edit", function (Operations $operation): array {
                return [
                'id' => $operation->getId(),
                ];
            });
            $action->setLabel("estimate_label")->setIcon('fas fa-pen-alt');
            return $action;
        });
    }


    public function configureFields(string $pageName): iterable
    {

        $operationId = null;

        // Essayer de récupérer l'ID de l'opération depuis la requête si on est sur la page d'édition
        if ($this->request->query->get('entityId')) {
            $operationId = $this->request->query->get('entityId');
        }

        $userFullName = '-';

        if ($operationId) {
            $usersRepository = $this->entityManager->getRepository(Users::class);
            $userName = $usersRepository->findUserNameByOperationAndRole($operationId, 'ROLE_USER');
            $userFullName = $userName ? $userName['firstname'] . ' ' . $userName['lastname'] : 'Utilisateur inconnu';
        }

        $userFullNameField = TextField::new('userFullName', 'Client')
            ->hideOnIndex()
            ->setFormTypeOption('mapped', false)
            ->setFormTypeOption('disabled', true)
            ->setFormTypeOption('attr', ['value' => $userFullName]);

        $service = AssociationField::new('services')
        ->setTemplatePath('employe\fields\service-type.html.twig')
        ->setLabel("operations_size_label")
        ->setColumns(6)
        ->setHelp('choose_operation_type_label')
        ->setFormTypeOptions([
            'class' => Services::class,
            'choice_label' =>'type',
            'required' => true
        ]);
        $price = NumberField::new('price')
        ->setLabel("price_label")
        ->setTemplatePath('employe\fields\price.html.twig')
        ->setHelp("please_estimate_label");





            return[
                FormField::addPanel('operation_detail_label')
                ->setColumns(6),
                    IdField::new('id')
                    ->hideOnForm(),
                    ChoiceField::new('quote')
                    ->setChoices([
                        'Estimation' => 'Estimation',
                        'Validation' => 'Validation',
                        'Validé' => 'Validé',
                        'Refusé' => 'Refusé',
                    ])
                    ->setLabel('quote_status_label')
                    ->setColumns(6)
                    ->setTemplatePath('admin/fields/field_status_quote.html.twig')
                    ->setFormTypeOptions([
                        'disabled' => true,
                        'required' => false]),
                    ChoiceField::new('status')
                    ->setLabel('operation_status_label')
                    ->setColumns(6)
                    ->setFormTypeOptions([
                        'disabled' => true,
                        'required' => false])
                    ->setTemplatePath('admin/fields/field_status_quote.html.twig')
                    ->setChoices([
                        'Disponible' => 'Disponible',
                        'En cours' => 'En cours',
                        'Terminée' => 'Terminée',
                        'Annulée' => 'Annulée'
                    ]),
                    $service,
                    NumberField::new('surface')
                    ->setLabel("Surface (m²)")
                    ->setColumns(6)
                    ->setFormTypeOptions([
                        'disabled' => true,
                        'required' => false]),
                    TextareaField::new('description')
                    ->setLabel("description_demand_label")
                    ->setColumns(12)
                    ->hideOnIndex()
                    ->setFormTypeOption('disabled', true),

                FormField::addPanel('client_detail_label'),
                    $userFullNameField
                    ->setColumns(12),
                    TextField::new('street')
                    ->setColumns(12)
                    ->setLabel("street_label")
                    ->hideOnIndex()
                    ->setFormTypeOptions([
                        'disabled' => true,
                        'required' => false]),
                    NumberField::new('zipcode')
                    ->setColumns(6)
                    ->setLabel("zipcode_label")
                    ->hideOnIndex()

                    ->setFormTypeOptions([
                        'disabled' => true,
                        'required' => false]),
                    TextField::new('city')
                    ->setLabel("city_label")
                    ->setColumns(6)
                    ->hideOnIndex()
                    ->setFormTypeOptions([
                        'disabled' => true,
                        'required' => false]),
                    DateField::new('createdAt')
                    ->setLabel("created_at_label")
                    ->setFormTypeOption('disabled', true)
                    ->setFormat('dd-MM-yy'),
                    DateTimeField::new('lastModifiedAt')
                    ->setLabel('modified_at_label')
                    ->setFormat('dd-MM-yy, HH:mm')
                    ->hideOnForm(),
                    $price
                ];
        }
    //Fin des confs




    //Fonction Edit
    #[Route("/redirect_or_deny_edit", name: "redirect_or_deny_edit")]
    public function redirectOrDenyEdit(AdminContext $context, AdminUrlGenerator $adminUrlGenerator): ?Response
    {
        //Je récupère l'ID de l'opération depuis le contextAdmin
        $operationId = ($context->getRequest()->get('id'));
        //le referer pour la redirection en cas d'erreur
        $request = $this->requestStack->getCurrentRequest();
        $referer = $request->headers->get('referer');
        //Je teste pour varier les flashs en fonction des erreurs
        if ($operationId) {
            $operation = $this->entityManager->getRepository(Operations::class)->find($operationId);
            // Vérification du statut de l'opération
            if ($operation->getQuote() === 'Validé') {
                // Le devis est validé, je refuse la redirection
                $this->addFlash("danger", "operationValidatedNoEstimation");
                return new RedirectResponse($referer);
            } elseif ($operation->getQuote() === 'Refusé') {
                // Le devis est refusé, je refuse la redirection
                $this->addFlash("danger", "quoteRefusedNoEstimation");
                return new RedirectResponse($referer);
            } else {
                // Je redirige vers PAGE::EDIT si tout est bon
                $editUrl = $adminUrlGenerator->setController(CommandsCrudController::class)->setAction(Crud::PAGE_EDIT);
                $editUrl->setEntityId($operationId);
                $redirectUrl = $editUrl->generateUrl();
                return new RedirectResponse($redirectUrl);
            }
        } else {
            // Au cas où, pour éviter la grosse erreur symfony, c'est pas plus beau mais ça fait le TAF. @TODO Rendre ça un peu plus joli
            return new Response('ID de l\'opération non fourni.', Response::HTTP_BAD_REQUEST);
        }
    }

    //Fonction pour récupérer l'opération à attribuer depuis l'URL
    public function getOperation(): Operations
    {
        $request = $this->requestStack->getCurrentRequest();
        //Je récupère l'ID de l'opération à attribuer
        $operationId = $request->query->get('entityId');
        $operation = $this->entityManager->getRepository(Operations::class)->find($operationId);
        return $operation;
    }

//Fonction test status
    public function testOperationStatusAndQuote( ?Operations $operation = null) : string
    {
        if($operation == null) {
        $operation = $this->getOperation();
        }

        if($operation->getStatus() !== 'Disponible' || $operation->getQuote() !== 'Validé' )
        {
            if($operation->getQuote() == 'Refusé' )
            {
                return "refuse";
            } else {
                return "false";
            }
        } else {
            return "true";
        }
    }

    //Fonction Attribution
    public function assignOperation(Permissions $permission, Users $user): bool
    {
        $result = $permission->testPermissions($user);
        $operation = $this->getOperation();

        //Si permissions est true après le test
        if ($result['success']) {
            $operation->addUserId($user);

            //Status de l'opération
            $operation->setStatus('En cours');

            $this->entityManager->persist($operation);
            $this->entityManager->flush();
            return true;
        } else { //False si jamais le nombre d'opération est dépassé
            return false;
        }
    }

    //Fonction pour prendre une opération
    public function takeOperation(Permissions $permission): Response
    {
        //Je récupère la requête pour la response et je set la redirection
        $request = $this->requestStack->getCurrentRequest();
        $referer = $request->headers->get('referer');

        //Je récupère l'ID de l'utilisateur connecté
        $user = $this->getUser();

        //Je teste le status de l'opération
        if ($this->testOperationStatusAndQuote() == "true") {

            //Test des permissions et assignation
            if ($this->assignOperation($permission, $user)) {
                $this->addFlash('success', "yourTurn");
                return new RedirectResponse($referer);
            } elseif ($this->assignOperation($permission, $user) == false) {
                $this->addFlash('warning', "toManyOperations");
                return new RedirectResponse($referer);
            }
        } elseif ($this->testOperationStatusAndQuote() ==  "refuse") {
            $this->addFlash('danger', "quoteRefused");
            return new RedirectResponse($referer);
        } else {
            //Si l'opération n'a pas le status demandé, je redirige et envoie le message demandé
            $this->addFlash('warning', "noAssign");
            return new RedirectResponse($referer);
        }
    }


//Fonction pour récupérer les utilisateurs ayant un rôle correspondant
    #[Route("/eligible-user", name:"eligible-user", methods:"GET")]
    public function getEligibleUsers(Permissions $permission)
    {
        $roles = ["ROLE_ADMIN","ROLE_SENIOR","ROLE_APPRENTI"];
        $queryBuilder = $this->entityManager->getRepository(Users::class)->findByRoles($roles);
        $query = $queryBuilder->getQuery();
        $eligibleUsers = $query->getResult();


        $userChoices = [];
        $occupiedId = [];
        foreach ($eligibleUsers as $user)
        {
            $result = $permission->testPermissions($user);
            $userLimit[$user->getId()] = [$result['permissions'], $result['ongoingOperations'] ];

            if ($result['success'])
            {
                $userChoices[$user->getUserIdentifier()] = $user->getId();
            } else {
                $userChoices[$user->getUserIdentifier()] = $user->getId();
                $occupiedId[]= $user->getId();
            }
            ;
        }

        return new JsonResponse([
            'userchoices' =>$userChoices,
            'occupiedId' =>$occupiedId,
            'limit/ongoing' => $userLimit,
        ]);
    }

    #[Route("/assignto", name:"assign-to", methods:"POST")]
    public function assignOperationTo(Request $request)
    {
        $entityId = $request->request->get('entityId');
        $selectedId = $request->request->get('selectedId');

        $selectedUser = $this->entityManager->getRepository(Users::class)->find($selectedId);
        $operation = $this->entityManager->getRepository(Operations::class)->find($entityId);

        //Je teste le status
        if ($this->testOperationStatusAndQuote($operation) == "true")
        {
            $operation->addUserId($selectedUser);

            //Status de l'opération
            $operation->setStatus('En cours');

            $this->entityManager->persist($operation);
            $this->entityManager->flush();

            $message = 'L\'opération a été attribuée avec succès à l\'employé.';
            $responseData = [
                'success' => true,
                'message' => $message
            ];
            return new JsonResponse($responseData);
        }
        elseif ($this->testOperationStatusAndQuote($operation) == "refuse")
        {
            $message = "Le devis de cette opération a été refusé";
            $responseData = [
                'success' => false,
                'message' => $message
            ];
            return new JsonResponse($responseData);
        }
        elseif  ($this->testOperationStatusAndQuote($operation) == "false")
        {
            //Si l'opération n'a pas le status demandé, je redirige et envoie le message demandé
            $message = "Cette opération ne peut pas encore être attribuée, un peu de patience !";
            $responseData = [
                'success' => false,
                'message' => $message
            ];
            return new JsonResponse($responseData);
        }
    }


    //Requête pour n'afficher que les "commandes"
    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $allowedQuote = ['Estimation', 'Validation', 'Validé', 'Refusé'];
        $excludedStatus = ['En cours', 'Terminée'];

        return parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters)
            ->andWhere('entity.quote IN (:approvedCommands)')
            ->andWhere('entity.status NOT IN  (:excludedStatus) OR entity.status IS NULL')
            ->setParameter('approvedCommands', $allowedQuote)
            ->setParameter('excludedStatus', $excludedStatus);
    }
//Ajout du JS qui permet l'affichage dynamique du prix
    public function configureAssets (Assets $assets): Assets
    {
            $assets->addJsFile('https://code.jquery.com/jquery-3.7.1.js');
            $assets->addJsFile('https://code.jquery.com/ui/1.13.2/jquery-ui.js');
            $assets->addJsFile('assets/js/flashMessages.js');
            $assets->addJsFile('assets/js/confirmTakeOperation.js');
            $assets->addCssFile('assets/css/flashMessages.css');
            $assets->addJsFile('assets/js/operation.js');
        return $assets;
    }
}

