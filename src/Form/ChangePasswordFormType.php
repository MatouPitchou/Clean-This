<?php
/**
 * @author: Mathilde Breux, Amélie Gattepaille
 */
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;

class ChangePasswordFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'options' => [
                    'attr' => [
                        'autocomplete' => 'new-password',
                    ],
                ],
                'first_options' => [
                    'constraints' => [
                        new Regex 
                        ('/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{6,}$/', 'Votre mot de passe doit contenir au moins 6 caractères, dont 1 majuscule, 1 minuscule, 1 chiffre et un caractère spécial (# ? ! @ $ % ^ & * -)')
                    ],
                    'label' => false,
                    'attr' => [
                        'class' =>'input',
                        // 'id' => 'toggle-password-new'
                    ],
                ],
                'second_options' => [
                    'label' => false,
                    'attr' => [
                        'class' =>'input',
                        // 'id' => 'toggle-password-repeat'
                    ],
                ],
                
                'invalid_message' => 'The password fields must match.',
                // Instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
