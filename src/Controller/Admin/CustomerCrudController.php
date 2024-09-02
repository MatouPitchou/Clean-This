<?php

/**
*
* @author: Dylan Rohart
*
*/


namespace App\Controller\Admin;

use App\Form\RoleType as FormRoleType;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class CustomerCrudController extends UsersCrudController
{

    public function __construct()
    {
    }

    public function configureFields(string $pageName): iterable
    {   
        $fullname = TextField::new('fullname', 'fullname_label')
                             ->hideOnForm();
        $role = ChoiceField::new('roles', 'Role')
                            ->setChoices([
                                'customer_label' => 'ROLE_USER',
                            ])
                            ->allowMultipleChoices(false)
                            ->setColumns(12)
                            ->setFormType(FormRoleType::class)
                            ->renderAsBadges([
                                'ROLE_USER' => 'success',
                            ]);
        $firstname = TextField::new('firstname', 'firstname_label')
                            ->hideOnIndex()
                            ->setColumns(6)
                            ->setFormTypeOption('attr', ['required' => 'required'])->setRequired(true);
        $lastname = TextField::new('lastname', 'lastname_label')
                            ->hideOnIndex()
                            ->setColumns(6)
                            ->setFormTypeOption('attr', ['required' => 'required'])->setRequired(true); 
        $mail = TextField::new('email')
                            ->setColumns(8);
        $phone = TextField::new('phone', 'phone_label')
                            ->setColumns(4)
                            ->setFormTypeOption('attr', ['required' => 'required'])->setRequired(true);
        $fulladdress = TextField::new('fullAddress', 'fulladdress_label')
                    ->hideOnForm();

                            /* Gestion de l'adresse  */
        $street = TextField::new('street', 'street_label')
                    ->hideOnIndex()
                    ->setColumns(12);
        $zipcode = TextField::new('zipcode', 'zipcode_label' )
                    ->setColumns(5)
                    ->setFormTypeOption('attr', ['required' => 'required'])
                    ->setRequired(true);
        $city = TextField::new('city', 'city_label')
                    ->hideOnIndex()
                    ->setColumns(7);


                            /* Gestion de l'adresse  */

        $createdAt = DateField::new('createdAt', 'created_at_label')
                                ->setFormat('d MMM. Y')
                                ->hideOnForm();
        $activeAt = DateTimeField::new('activeAt', 'active_at_label')
                                ->setFormat('dd-MM-yy, HH:mm')
                                ->hideOnForm();

                        
        if (Crud::PAGE_INDEX === $pageName) {
            return [
                $fullname, $mail, $phone, $role, $zipcode, $fulladdress, $createdAt, $activeAt
            ];
        } elseif (Crud::PAGE_EDIT === $pageName) {
            return [
                FormField::addPanel('personal_info_label')->addCssClass('column col-md-5'),
                    $firstname, $lastname, $mail, $phone, $role,
                FormField::addPanel('address_label')->addCssClass('column col-md-5'),
                $street, $zipcode, $city
            ];
        }
    }

    
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
        ->setPageTitle('index', 'customer_label')
        ->setPageTitle('edit', 'edit_customer_label')
        ->overrideTemplate('crud/new', 'admin/fields/forms/displayNewPassword.html.twig')
        ->overrideTemplate('crud/new', 'admin/fields/forms/autocompleteAddressNew.html.twig')
        ->overrideTemplate('crud/edit', 'admin/fields/forms/autocompleteAddressEdit.html.twig');
    }
    
    public function configureActions(Actions $actions): Actions
    {
        return $actions
            // ...
            ->remove(Crud::PAGE_INDEX, Action::NEW)
            ->update(Crud::PAGE_NEW, Action::SAVE_AND_RETURN, function (Action $action) {
                return $action->setLabel('create_label')->setIcon('fa fa-plus');
            })
            ->update(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER, function (Action $action) {
                return $action->setLabel('add_more_label')->setIcon('fa fa-pen-to-square');
            })
            ->update(Crud::PAGE_EDIT, Action::SAVE_AND_RETURN, function (Action $action) {
                return $action->setLabel('edit_label')->setIcon('fa fa-plus');
            })
            ->update(Crud::PAGE_INDEX, Action::EDIT, function (Action $action) {
                return $action->setLabel("edit_label");
            })
            ->update(Crud::PAGE_EDIT, Action::SAVE_AND_CONTINUE, function (Action $action) {
                return $action->setLabel('add_more_label')->setIcon('fa fa-pen-to-square');
            })
            ;
            
        ;
    }


    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
        
        $roles = ['ROLE_USER', ];

        $orX = $qb->expr()->orX();

        foreach ($roles as $key => $role) {
            $roleParam = ':role' . $key;
            $orX->add($qb->expr()->like('entity.roles', $roleParam));
            $qb->setParameter($roleParam, '%"'.$role.'"%');
        }

        $qb->andWhere($orX);
        return $qb;
    }
}