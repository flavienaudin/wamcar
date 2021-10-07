<?php


namespace AppBundle\Form\Type;


use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Wamcar\User\ProUser;

class VideoProjectCoworkerType extends EntityType
{

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults([
            'class' => ProUser::class,
            'multiple' => true,
            'expanded' => true,
            'choice_label' => 'fullName',
            'choice_value' => 'id'

        ]);
    }
}