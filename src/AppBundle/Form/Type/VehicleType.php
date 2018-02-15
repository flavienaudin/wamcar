<?php

namespace AppBundle\Form\Type;

use AppBundle\Form\DTO\VehicleDTO;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VehicleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('vehicleRegistration', VehicleRegistrationType::class, [
                'required' => false,
                'error_bubbling' => true,
            ])
            ->add('information', VehicleInformationType::class, [
                'available_values' => $options['available_values'],
                'error_bubbling' => true,
            ])
            ->add('specifics', VehicleSpecificsType::class, [
                'error_bubbling' => true,
            ])
            ->add('pictures', CollectionType::class, [
                'label' => false,
                'entry_type' => VehiclePictureType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'required' => false,
                'error_bubbling' => true,
            ])
            ->add('vehicleReplace', HiddenType::class)
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => VehicleDTO::class,
            'translation_domain' => 'registration'
        ]);
        $resolver->setRequired('available_values');
    }


}
