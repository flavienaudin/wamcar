<?php


namespace AppBundle\Form\Type\SpecificField;


use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Wamcar\User\ProService;

class ProServiceCategoryServicesSelectType extends EntityType
{

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults([
            'class' => ProService::class,
            'multiple' => true,
            'expanded' => true,
            'choice_label' => 'name',
            'choice_value' => 'slug'
        ]);
    }
}