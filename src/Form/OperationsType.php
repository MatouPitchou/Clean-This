<?php

namespace App\Form;

use App\Entity\Operations;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class OperationsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Accéder à l'utilisateur actuel passé comme option
        $currentUser = $options['current_user'];

        $builder
            ->add('zipcode', TextType::class, [
                'label' => 'Code Postal',
                'required' => false,
                // Utiliser les informations de l'utilisateur actuel par défaut
                'data' => $currentUser ? $currentUser->getZipcode() : null,
            ])
            ->add('city', TextType::class, [
                'label' => 'Ville',
                'required' => false,
                // Utiliser les informations de l'utilisateur actuel par défaut
                'data' => $currentUser ? $currentUser->getCity() : null,
            ])
            ->add('street', TextType::class, [
                'label' => 'Rue',
                'required' => false,
                // Utiliser les informations de l'utilisateur actuel par défaut
                'data' => $currentUser ? $currentUser->getStreet() : null,
            ])
            ->add('surface', NumberType::class, [
                'label' => "Surface en m²",
            ])
            ->add('description', TextareaType::class, [
                'label' => "Description",
                'constraints' => [
                    new NotBlank([
                        'message' => "Veuillez saisir une courte description"
                    ])
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Operations::class,
            'current_user' => null, // Définir une valeur par défaut pour l'utilisateur actuel
        ]);
        
        // Valider que l'option 'current_user' est bien une instance de UserInterface
        $resolver->setAllowedTypes('current_user', [UserInterface::class, 'null']);
    }
}
