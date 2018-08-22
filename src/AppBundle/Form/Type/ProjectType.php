<?php

namespace AppBundle\Form\Type;

use AppBundle\Form\DTO\ProjectDTO;
use AppBundle\Form\Type\SpecificField\AmountType;
use AppBundle\Form\Type\Traits\AutocompleteableCityTrait;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProjectType extends AbstractType
{
    use AutocompleteableCityTrait;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $availableMakes = $options['available_makes'] ?? [];
        $availableModels = $options['available_models'] ?? [];

        $builder
            ->add('isFleet', ChoiceType::class, [
                'expanded' => true,
                'choices' => [
                    'Véhicule unique' => false,
                    'Une flotte' => true
                ],
                'error_bubbling' => true
            ])
            ->add('budget', AmountType::class, [
                'error_bubbling' => true,
                'required' => false
            ])
            ->add('description', TextareaType::class, [
                'error_bubbling' => true,
                'required' => false
            ])
            ->add('projectVehicles', CollectionType::class, [
                'label' => false,
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
            ]);
        $this->addAutocompletableCityField($builder, $builder->getData());
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
