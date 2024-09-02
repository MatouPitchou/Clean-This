<?php

namespace App\Form;

use App\Entity\Invoices;
use App\Entity\operations;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InvoicesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('paid')
            ->add('slug')
            ->add('path')
            ->add('createdAt', null, [
                'widget' => 'single_text',
            ])
            ->add('paidAt', null, [
                'widget' => 'single_text',
            ])
            ->add('operation', EntityType::class, [
                'class' => operations::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Invoices::class,
        ]);
    }
}
