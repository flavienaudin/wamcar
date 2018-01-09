<?php

namespace AppBundle\Form\Type;

use AppBundle\Form\DTO\ProjectDTO;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProjectType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $availableMakes = $options['available_makes'] ?? [];
        $availableModels = $options['available_models'] ?? [];

        $builder
            ->add('isFleet', ChoiceType::class, [
                'expanded' => true,
                'choices' => [
                    'Véhicule unique',
                    'Une flotte'
                ],
                'error_bubbling' => true,
                'data' => 'Véhicule unique'
            ])
            ->add('budget', IntegerType::class, [
                'error_bubbling' => true,
                'required' => false
            ])
            ->add('description', TextareaType::class, [
                'error_bubbling' => true,
                'required' => false
            ])
            ->add('projectVehicles', CollectionType::class, [
                'entry_type' => ProjectVehicleType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'required' => false,
                'error_bubbling' => true,
                'entry_options' => [
                    'available_makes' => $availableMakes,
                    'available_models' => $availableModels,
                    'label' => false
                ]
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
        $resolver->setRequired('available_makes');
        $resolver->setRequired('available_models');
    }
}
