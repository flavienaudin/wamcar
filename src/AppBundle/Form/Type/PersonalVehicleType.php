<?php

namespace AppBundle\Form\Type;

use AppBundle\Form\DTO\PersonalVehicleDTO;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PersonalVehicleType extends VehicleType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder
            ->add('userRegistration', RegistrationType::class, [
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
            'data_class' => PersonalVehicleDTO::class,
            'translation_domain' => 'registration',
            'allow_extra_fields' => true
        ]);
        $resolver->setRequired('available_values');
    }


}
