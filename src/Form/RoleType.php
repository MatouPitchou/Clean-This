<?php

/*
*
* @author: Dylan Rohart
*
*/
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface as FormFormBuilderInterface;

class RoleType extends AbstractType
{
    public function buildForm(FormFormBuilderInterface $builder, array $options)
    {   
        parent::buildForm($builder, $options);
        $builder
            ->addModelTransformer(new CallbackTransformer(
                function ($originalRoles){
                   return ($originalRoles) ? $originalRoles[0] : null;
                },
                function($submmitedRoles){      
                    return array_filter([$submmitedRoles]);
                }
            ))
        ;
    }
    public function getParent()
    {
        return ChoiceType::class;
    }
}