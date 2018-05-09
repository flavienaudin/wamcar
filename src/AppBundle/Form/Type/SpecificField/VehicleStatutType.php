<?php

namespace AppBundle\Form\Type\SpecificField;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VehicleStatutType extends ChoiceType
{
    const NEW = 'VEHICLE_STATUT.FIRST_HAND';
    const SECOND_HAND = 'VEHICLE_STATUT.SECOND_HAND';

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('choices', [self::NEW, self::SECOND_HAND]);
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
        return 'yesno';
    }
}
