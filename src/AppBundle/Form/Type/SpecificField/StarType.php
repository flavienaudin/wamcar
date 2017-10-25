<?php

namespace AppBundle\Form\Type\SpecificField;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StarType extends ChoiceType
{
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('choices', [
            'Très mauvais' => '1',
            'Mauvais' => '2',
            'Moyen' => '3',
            'Bon' => '4',
            'Très bon' => '5'
        ]);
        $resolver->setDefault('placeholder', false);
        $resolver->setDefault('expanded', true);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return ChoiceType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'star';
    }
}
