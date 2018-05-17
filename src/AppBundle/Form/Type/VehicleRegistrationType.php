<?php

namespace AppBundle\Form\Type;


use AppBundle\Form\DTO\VehicleRegistrationDTO;
use AppBundle\Form\Type\SpecificField\VINType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VehicleRegistrationType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('plateNumber', TextType::class, [
                'required' => false,
                'error_bubbling' => true,
            ])
            ->add('vin', VINType::class, [
                'required' => false,
                'error_bubbling' => true,
            ]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => VehicleRegistrationDTO::class,
            'translation_domain' => 'registration'
        ]);
    }
}