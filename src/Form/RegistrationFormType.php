<?php
/**
 * Structure:
 * @author: Mathilde Breux
 */
namespace App\Form;

use App\Entity\Users;
use Symfony\Component\Form\AbstractType;
use Composer\Semver\Constraint\Constraint;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'attr' => [
                    'class' => 'form-input',
                    'placeholder' => 'E-Mail',
                ],
                'label' => false,
            ])
            ->add('lastname', TextType::class, [
                'attr' => [
                    'class' => 'form-input',
                    'placeholder' => 'Nom',
                ],
                'label' => false,
            ])
            ->add('firstname', TextType::class, [
                'attr' => [
                    'class' => 'form-control form-input',
                    'placeholder' => 'Prénom',
                ],
                'label' => false,
            ])
            ->add('phone', TextType::class, [
                'attr' => [
                    'class' => 'form-control form-input',
                    'placeholder' => 'Téléphone',
                ],
                'label' => false,
            ])
            ->add('zipcode', TextType::class, [
                'attr' => [
                    'class' => 'form-control form-input',
                    'placeholder' => 'Code postal',
                ],
                'label' => false,
            ])
            ->add('city', TextType::class, [
                'attr' => [
                    'class' => 'form-control form-input',
                    'placeholder' => 'Ville',
                ],
                'label' => false,
            ])
            ->add('street', TextType::class, [
                'attr' => [
                    'class' => 'form-input',
                    'placeholder' => 'Adresse',
                    'autocomplete' => 'off',
                ],
                'label' => false,
            ])
            ->add('agreeTerms', CheckboxType::class, [
                                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'Vous devez accepter les conditions d\'utilisations.',
                    ]),
                ],
                'label' => 'Accepter les conditions d\'utilisations'
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password'
                ],
                 'first_options'  => [
                    'label' => false,
                    'attr' => ['placeholder' => 'Mot de passe',
                    'class' => 'form-control form-input'],
                ],
                'second_options' => [
                    'label' => false,
                    'attr' => ['placeholder' => 'Répéter le mot de passe',
                    'class' => 'form-control form-input'],
                ],
                'constraints' => [
                    new Regex 
                    ('/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{6,}$/', 'Votre mot de passe doit contenir au moins 6 caractères, dont 1 majuscule, 1 minuscule, 1 chiffre et un caractère spécial (# ? ! @ $ % ^ & * -)')
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Users::class,
        ]);
    }
}
