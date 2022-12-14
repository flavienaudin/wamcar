<?php

namespace AppBundle\Form\Type;

use AppBundle\Form\DTO\PersonalVehicleDTO;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PersonalVehicleType extends VehicleType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => PersonalVehicleDTO::class,
            'translation_domain' => 'registration'
        ]);
        $resolver->setRequired('available_values');
    }


}
