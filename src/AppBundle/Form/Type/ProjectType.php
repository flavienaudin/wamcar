<?php

namespace AppBundle\Form\Type;

use AppBundle\Form\DTO\ProjectDTO;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProjectType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type', ChoiceType::class, [
                'choices' => \Wamcar\User\ProjectType::toArray(),
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
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ProjectDTO::class,
            'translation_domain' => 'user'
        ]);
        $resolver->setRequired('available_values');
    }


}
