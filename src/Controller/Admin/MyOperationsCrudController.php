<?php

namespace App\Controller\Admin;

use App\Entity\Users;
use App\Entity\Services;
use App\Entity\Operations;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use App\Controller\Admin\OperationsCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;

class MyOperationsCrudController extends OperationsCrudController
{
    public function getUserID()
    {
        $user = $this->getUser();

        if ($user instanceof Users) {
            $userId = $user->getId();
            return $userId;
        }
    }

    //Configure CRUD, Actions & Fields 
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInPlural('Opérations')
            ->setEntityLabelInSingular('Opération')
            ->setPageTitle('index', 'personal_history_label');
    }
    public function configureActions(Actions $actions): Actions
    {

        $updateInvoice = Action::new('updateInvoice', 'Paiement facture', 'fas fa-credit-card')
            ->linkToRoute('updateInvoice', function (Operations $operation) {
                return [
                    'operationId' => $operation->getId(),
                ];
            })
            ->displayIf(function (Operations $operation) {
                return $operation->getInvoices() !== null && !$operation->getInvoices()->isPaid();
            })
            ->setHtmlAttributes(['id' => 'updateInvoice-button']);

        $showInvoice = Action::new('showInvoice', 'Voir la facture', 'fas  fa-file-invoice')
            ->linkToRoute('showInvoice', function (Operations $operation) {
                return [
                    'operationId' => $operation->getId(),
                ];
            })
            ->displayIf(function (Operations $operation) {
                return $operation->getInvoices() !== null && $operation->getStatus() === "Terminée";
            })
            ->setHtmlAttributes(['target' => '_blank']);


        return $actions
            ->add(Crud::PAGE_EDIT, $updateInvoice)
            ->add(Crud::PAGE_EDIT, $showInvoice)
            ->remove(Crud::PAGE_INDEX, Action::NEW)
            ->remove(Crud::PAGE_INDEX, Action::DELETE)
            ->update(Crud::PAGE_INDEX, Action::EDIT, function (Action $action) {
                return $action->setLabel("edit_label");
            })
            ->remove(Crud::PAGE_EDIT, Action::SAVE_AND_CONTINUE)
            ->update(Crud::PAGE_EDIT, Action::SAVE_AND_RETURN, function (Action $action) {
                return $action->setLabel("save_label");
            });
    }
    public function configureFields(string $pageName): iterable
    {
        $service = AssociationField::new('services')
            ->setTemplatePath('employe\fields\service-type.html.twig')
            ->setLabel("operations_size_label")
            ->setFormTypeOption('disabled', true)
            ->setFormTypeOptions([
                'class' => Services::class,
                'choice_label' => 'type'
            ]);
        $price = NumberField::new('price')
            ->hideOnForm()
            ->setTemplatePath('employe\fields\price.html.twig')
            ->setFormTypeOption('disabled', true)
            ->setLabel("price_label");
       /* $isPaid = BooleanField::new('invoices.paid', 'Facture payée')
            ->setTemplatePath('admin/fields/custom_boolean.html.twig');
         ->renderAsSwitch()
            ->hideOnIndex()
            ->setFormTypeOption('disabled', true); */

        return [
            FormField::addPanel('operation_detail_label'),
            IdField::new('id')
                ->setLabel("ID")
                ->setFormTypeOption('disabled', true),
            ChoiceField::new('quote')
                ->setChoices([
                    'En attente d\'Estimation' => 'Estimation',
                    'En attente de Validation' => 'Validation',
                    'Validé' => 'Validé',
                    'Refusé' => 'Refusé',
                ])
                ->setTemplatePath('admin/fields/field_status_quote.html.twig')
                ->renderAsBadges([

                    'Estimation' => 'warning',
                    'Validation' => 'warning',
                    'Validé' => 'success',
                    'Refusé' => 'danger'
                ])
                ->setLabel('quote_status_label')
                ->setFormTypeOption('disabled', true),
            ChoiceField::new('status')
                ->setLabel('operation_status_label')
                ->setChoices([
                    'badge_disponible_label' => 'Disponible',
                    'badge_encours_label' => 'En cours',
                    'badge_terminee_label' => 'Terminée',
                    'badge_annulee_label' => 'Annulée'
                ])
                ->setFormTypeOption('disabled', false)
                ->setFormTypeOption('required', true)
                ->setHelp("change_status_label")
                ->renderAsBadges([
                    'Disponible' => 'info',
                    'En cours' => 'warning',
                    'Annulée' => 'danger',
                    'Terminée' => 'success'
                ])
                ->setTemplatePath('admin/fields/field_status_quote.html.twig'),
            $service,
            TextareaField::new('description')
                ->setLabel("description_demand_label")
                ->hideOnIndex()
                ->setFormTypeOption('disabled', true),
            DateField::new('createdAt')
                ->setLabel("created_at_label")
                ->setFormTypeOption('disabled', true)
                ->setFormat('dd-MM-yy'),
            DateTimeField::new('lastModifiedAt')
                ->setLabel('modified_at_label')
                ->setFormat('dd-MM-yy, HH:mm')
                ->hideOnForm(),
            $price,
            // $isPaid,
        ];
    }

    //Requête pour n'afficher que les operations ayant le devis validé et dont l'identifiant et l'ID de l'utilisateur. 

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {

        $userId = $this->getUserID();

        return parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters)
            ->andWhere('entity.quote = :approvedQuote')
            ->setParameter('approvedQuote', 'validé')
            ->innerJoin('entity.users', 'users')
            ->andWhere('users.id = :approvedUser')
            ->setParameter('approvedUser', $userId);
    }

    public function configureAssets(Assets $assets): Assets
    {
        $assets->addJsFile('assets/js/sendInvoice.js');
        $assets->addJsFile('assets/js/confirmCancelOperation.js');

        return $assets;
    }
}
