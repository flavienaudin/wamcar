<?php

namespace AppBundle\Form\Type;

use AppBundle\Form\DTO\UserRegistrationPersonalVehicleDTO;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserRegistrationPersonalVehicleType extends PersonalVehicleType
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
            'data_class' => UserRegistrationPersonalVehicleDTO::class,
            'translation_domain' => 'registration'
        ]);
        $resolver->setRequired('available_values');
    }


}
