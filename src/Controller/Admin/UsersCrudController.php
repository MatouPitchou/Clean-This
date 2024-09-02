<?php

/**
*
* @author: Dylan Rohart
*
*/



namespace App\Controller\Admin;

use App\Entity\Users;
use Doctrine\ORM\QueryBuilder;
use App\Form\RoleType as FormRoleType;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use Symfony\Contracts\Translation\TranslatorInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class UsersCrudController extends AbstractCrudController
{

    public function __construct()
    {
    }

    public static function getEntityFqcn(): string
    {
        return Users::class;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions

            ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                return $action->setLabel('add_label')->setIcon('fa fa-plus');
            })
            ->update(Crud::PAGE_INDEX, Action::EDIT, function (Action $action) {
                return $action->setLabel("edit_label");
            })
            ->update(Crud::PAGE_INDEX, Action::DELETE, function (Action $action) {
                return $action
                    ->setLabel('Supprimer')
                    ->displayIf(function (Users $user) {
                        return !$this->isUserAdmin($user);
                    });
            })
            ->update(Crud::PAGE_NEW, Action::SAVE_AND_RETURN, function (Action $action) {
                return $action->setLabel('create_label')->setIcon('fa fa-plus');
            })
            ->update(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER, function (Action $action) {
                return $action->setLabel('add_more_label')->setIcon('fa fa-pen-to-square');
            })
            ->update(Crud::PAGE_EDIT, Action::SAVE_AND_RETURN, function (Action $action) {
                return $action->setLabel('edit_label')->setIcon('fa fa-plus');
            })
            ->update(Crud::PAGE_EDIT, Action::SAVE_AND_CONTINUE, function (Action $action) {
                return $action->setLabel('add_more_label')->setIcon('fa fa-pen-to-square');
            })
            ;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
        ->setPageTitle('index', 'employe_label')
        ->setPageTitle('new', 'create_employe_label')
        ->setPageTitle('edit', 'edit_employe_label')
        ->setFormThemes(['admin/fields/forms/custom_password.html.twig', '@EasyAdmin/crud/form_theme.html.twig'])
        ->overrideTemplate('crud/new', 'admin/fields/forms/displayNewPassword.html.twig')
        ->overrideTemplate('crud/new', 'admin/fields/forms/autocompleteAddressNew.html.twig')
        ->overrideTemplate('crud/edit', 'admin/fields/forms/autocompleteAddressEdit.html.twig');

    }

    public function configureFields(string $pageName): iterable
    {
        $fullname = TextField::new('fullname', 'fullname_label')
                             ->hideOnForm();
        $role = ChoiceField::new('roles', 'Role')
                            ->setChoices([
                                'Expert' => 'ROLE_ADMIN',
                                'senior_label' => 'ROLE_SENIOR',
                                'apprenti_label' => 'ROLE_APPRENTI'
                            ])
                            ->allowMultipleChoices(false)
                            ->setColumns(12)
                            ->setFormType(FormRoleType::class)
                            ->renderAsBadges([
                                'ROLE_ADMIN' => 'success',
                                'ROLE_SENIOR' => 'info',
                                'ROLE_APPRENTI' => 'warning'
                            ]);
        $firstname = TextField::new('firstname', 'firstname_label')
                            ->hideOnIndex()
                            ->setColumns(6)
                            ->setFormTypeOption('attr', ['required' => 'required'])->setRequired(true);
        $lastname = TextField::new('lastname', 'lastname_label')
                            ->hideOnIndex()
                            ->setColumns(6)
                            ->setFormTypeOption('attr', ['required' => 'required'])->setRequired(true);
        $mail = TextField::new('email', 'mail_label')
                            ->setColumns(8);
        $phone = TextField::new('phone', 'phone_label')
                            ->setColumns(4)
                            ->setFormTypeOption('attr', ['required' => 'required'])->setRequired(true);
        $fulladdress = TextField::new('fullAddress', 'fulladdress_label')
                            ->hideOnForm();

                            /* Gestion de l'adresse  */

        $zipcode = TextField::new('zipcode', 'zipcode_label' )
                            ->setColumns(5)
                            ->setFormTypeOption('attr', ['required' => 'required'])
                            ->setRequired(true);
        $city = TextField::new('city', 'city_label')
                            ->hideOnIndex()
                            ->setColumns(7);
        $street = TextField::new('street', 'street_label')
                            ->hideOnIndex()
                            ->setColumns(12);

                            /* Gestion de l'adresse  */

        $password = TextField::new('generatedPassword', 'temporary_password_label')
                    ->setFormType(PasswordType::class)
                    ->setFormTypeOptions([
                        'block_name' => 'custom_password',
                    ])
                    ->setTemplatePath('admin/fields/password_field_with_toggle.html.twig')
                    ->onlyWhenCreating()
                    ->setColumns(12)
                    ->setFormTypeOptions(['disabled' => true, 'attr' => ['readonly' => true]])
                    ->onlyWhenCreating();
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
                FormField::addPanel('Adresse')->addCssClass('column col-md-5'),
                    $street, $zipcode, $city
            ];

        } elseif (Crud::PAGE_NEW === $pageName) {
            return [
                FormField::addPanel('personal_info_label')->addCssClass('column col-md-5'),
                    $firstname, $lastname, $mail, $phone, $role,
                FormField::addPanel('Adresse')->addCssClass('column col-md-5'),
                    $street, $zipcode, $city, $password
            ];
        }
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);

        $roles = ['ROLE_ADMIN', 'ROLE_SENIOR', 'ROLE_APPRENTI'];

        $orX = $qb->expr()->orX();

        foreach ($roles as $key => $role) {
            $roleParam = ':role' . $key;
            $orX->add($qb->expr()->like('entity.roles', $roleParam));
            $qb->setParameter($roleParam, '%"'.$role.'"%');
        }

        $qb->andWhere($orX);
        return $qb;
    }

    public function deleteEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof Users) {
            // Vérifier si l'utilisateur n'est pas un administrateur
            if (!$this->isUserAdmin($entityInstance)) {
                // Si c'est le cas, supprimer l'utilisateur
                parent::deleteEntity($entityManager, $entityInstance);
                $this->addFlash('success', 'userDeleted');
                return;
            }

            // Ajouter un message flash pour informer l'utilisateur
            $this->addFlash('danger', 'adminNotDeleted');
            // Ne pas poursuivre avec la suppression
            return;
    }
}

    private function isUserAdmin(Users $user): bool
    {
        return in_array('ROLE_ADMIN', $user->getRoles());
    }

    public function configureAssets(Assets $assets): Assets
    {
        $assets->addJsFile('assets/js/flashMessages.js');
        $assets->addCssFile('assets/css/flashMessages.css');

        return $assets;
    }
}
