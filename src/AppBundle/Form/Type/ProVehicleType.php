<?php

namespace AppBundle\Form\Type;

use AppBundle\Form\DTO\ProVehicleDTO;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProVehicleType extends VehicleType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder
            ->add('offer', VehicleOfferType::class, [
                'error_bubbling' => true,
            ])
            ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ProVehicleDTO::class,
            'translation_domain' => 'registration'
        ]);
        $resolver->setRequired('available_values');
    }


}
