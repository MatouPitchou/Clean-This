<?php

namespace App\Controller\Admin;

use App\Entity\Operations;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class OperationsCrudController extends AbstractCrudController
{


    public static function getEntityFqcn(): string
    {
        return Operations::class;
    }

        //Configure CRUD, Actions & Fields
        public function configureCrud(Crud $crud): Crud
        {
            return $crud
            ->setEntityLabelInPlural('Opérations')
            ->setEntityLabelInSingular('Opération')
            ->setPageTitle('index', 'general_hystory_label')
            ->setPageTitle('detail', 'operation_detail_label')
            ->showEntityActionsInlined();
        }
        public function configureActions(Actions $actions): Actions
        {

            return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(Crud::PAGE_INDEX, Action::DETAIL, function (Action $action) {
                return $action->setLabel("")->setIcon("fa-solid fa-magnifying-glass");
            })
            ->update(Crud::PAGE_DETAIL, Action::INDEX, function (Action $action) {
                return $action ->setLabel("back_label");
            })
            ->disable(Action::NEW, Action::DELETE, Action::EDIT, Action::SAVE_AND_CONTINUE);
        }

    public function configureFields(string $pageName): iterable
    {
        $service = AssociationField::new('services')
        ->setTemplatePath('employe\fields\service-type.html.twig')
        ->setLabel("operations_size_label");
        $price = NumberField::new('price')
        ->setTemplatePath('employe\fields\price.html.twig')
        ->setLabel("price_label");
        return[
            // FormField::addColumn(6, "Informations générales"),
            formField::addPanel("information_title_label"),
                IdField::new('id')
                ->setLabel("operation_id")
                ->hideOnForm(),
            // FormField::addColumn(6),
                TextField::new('EmployeName')
                ->setLabel("employe_label")
                ->setTemplatePath('admin/fields/field_employe_name.html.twig')
                ->setFormTypeOption('disabled', true),
                ChoiceField::new('quote')
                ->setChoices([
                    'En attente d\'Estimation' => 'Estimation',
                    'En attente de Validation' => 'Validation',
                    'Validé' => 'Validé',
                    'Refusé' => 'Refusé',
                ])
                ->renderAsBadges([
                'Estimation' => 'warning',
                'Validation' => 'warning',
                'Validé' => 'success',
                'Refusé' => 'danger'])
                ->setLabel('quote_status_label')
                ->setTemplatePath('admin/fields/field_status_quote.html.twig')
                ->setFormTypeOption('disabled', true),
                ChoiceField::new('status')
                ->setLabel('operation_status_label')
                ->setTemplatePath('admin/fields/field_status_quote.html.twig')
                ->setChoices([
                    'Disponible' => 'Disponible',
                    'En cours' => 'En cours',
                    'Terminée' => 'Terminée',
                    'Annulée' => 'Annulée'
                ])
                ->setFormTypeOption('disabled', false)
                ->setFormTypeOption('required', true)
                ->renderAsBadges([
                    'Disponible' => 'info',
                    'En cours' => 'warning',
                    'Annulée' => 'danger',
                    'Terminée' => 'success']),
                $service,
                $price,
                DateField::new('createdAt')
                ->setLabel("created_at_label")
                ->setFormTypeOption('disabled', true)
                ->setFormat('dd-MM-yy'),
                DateTimeField::new('lastModifiedAt')
                ->setLabel('modified_at_label')
                ->setFormat('dd-MM-yy, HH:mm')
                ->hideOnForm(),
                DateTimeField::new('finishedAt')
                ->setLabel('finished_at_label')
                ->setFormat('dd-MM-yy, HH:mm'),
                FormField::addPanel('description_label'),
                TextareaField::new('description')
                ->setLabel("")
                ->hideOnIndex()
                ->setFormTypeOption('disabled', true),
            ];

    }


    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        return parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
    }



}
