<?php

namespace AppBundle\Form\Type\SpecificField;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VehicleStatutType extends ChoiceType
{
    const NEW = 'VEHICLE_STATUT.NEW';
    const USED = 'VEHICLE_STATUT.USED';

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('choices', [self::NEW, self::USED]);
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
