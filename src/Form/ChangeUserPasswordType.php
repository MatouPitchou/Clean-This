<?php
/**
 * @author: Mathilde Breux
 */
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;

class ChangeUserPasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('plainPassword', PasswordType::class, [
                'attr' => [
                    'class' =>'input',
                    'id' => 'toggle-password-plain'
                ],
                'label' => false
            ])

            ->add('newPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => [
                    'constraints' => [
                        new Regex 
                        ('/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{6,}$/', 'Votre mot de passe doit contenir au moins 6 caractères, dont 1 majuscule, 1 minuscule, 1 chiffre et un caractère spécial (# ? ! @ $ % ^ & * -)')
                    ],
                    'attr' => [
                        'class' =>'input',
                        'id' => 'toggle-password-new'
                    ],
                    'label' => false],
                'second_options' => [
                    'attr' => [
                        'class' => 'input',
                        'id' => 'toggle-password-repeat'
                    ],
                    'label' => false],
                'invalid_message' => 'Les mots de passe ne correspondent pas.',
            ])

            ->add('submit', SubmitType::class, [
                'attr' => [
                    'class' => 'button'
                ],
                'label' => "Enregistrer"
            ])
        ;
    }

    public function configureAssets (Assets $assets): Assets
    {        
            $assets->addJsFile('assets/js/showPassword.js');

        return $assets;
    }  
}