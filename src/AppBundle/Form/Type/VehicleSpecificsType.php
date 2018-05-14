<?php

namespace AppBundle\Form\Type;

use AppBundle\Form\DataTransformer\EnumDataTransformer;
use AppBundle\Form\DataTransformer\VehicleStatutDataTransformer;
use AppBundle\Form\DataTransformer\YesNoDataTransformer;
use AppBundle\Form\DTO\VehicleSpecificsDTO;
use AppBundle\Form\Type\SpecificField\AmountType;
use AppBundle\Form\Type\SpecificField\StarType;
use AppBundle\Form\Type\SpecificField\VehicleStatutType;
use AppBundle\Form\Type\SpecificField\YesNoType;
use AppBundle\Form\Type\Traits\AutocompleteableCityTrait;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\{
    ChoiceType, DateType, TextareaType
};
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Wamcar\Vehicle\Enum\MaintenanceState;
use Wamcar\Vehicle\Enum\SafetyTestDate;
use Wamcar\Vehicle\Enum\SafetyTestState;
use Wamcar\Vehicle\Enum\TimingBeltState;

class VehicleSpecificsType extends AbstractType
{
    use AutocompleteableCityTrait;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $data = $builder->getData();

        $builder
            ->add('registrationDate', DateType::class, [
                'error_bubbling' => true,
                'html5' => false,
                'widget' => 'single_text',
                'attr' => [
                    'data-view' => 'years',
                    'data-date-format' => 'yyyy-mm-dd'
                ]
            ])
            ->add('isUsed', VehicleStatutType::class, [
                'required' => true,
                'error_bubbling' => true
            ])
            ->add('mileage', AmountType::class, [
                'error_bubbling' => true,
            ])
            ->add('timingBeltState', ChoiceType::class, [
                'required' => false,
                'choices' => TimingBeltState::toArray(),
                'choice_translation_domain' => 'enumeration',
                'error_bubbling' => true,
            ])
            ->add('safetyTestDate', ChoiceType::class, [
                'required' => false,
                'choices' => SafetyTestDate::toArray(),
                'choice_translation_domain' => 'enumeration',
                'error_bubbling' => true,
            ])
            ->add('safetyTestState', ChoiceType::class, [
                'required' => false,
                'choices' => SafetyTestState::toArray(),
                'choice_translation_domain' => 'enumeration',
                'error_bubbling' => true,
            ])
            ->add('bodyState', StarType::class, [
                'required' => false,
                'error_bubbling' => true,
            ])
            ->add('engineState', StarType::class, [
                'required' => false,
                'error_bubbling' => true,
            ])
            ->add('tyreState', StarType::class, [
                'required' => false,
                'error_bubbling' => true,
            ])
            ->add('maintenanceState', ChoiceType::class, [
                'required' => false,
                'choices' => MaintenanceState::toArray(),
                'choice_translation_domain' => 'enumeration',
                'error_bubbling' => true,
            ])
            ->add('isImported', YesNoType::class, [
                'required' => false,
                'error_bubbling' => true
            ])
            ->add('isFirstHand', YesNoType::class, [
                'required' => false,
                'error_bubbling' => true
            ])
            ->add('additionalInformation', TextareaType::class, [
                'required' => false,
                'error_bubbling' => true,
            ]);

        $builder->get('timingBeltState')->addModelTransformer(new EnumDataTransformer(TimingBeltState::class));
        $builder->get('safetyTestDate')->addModelTransformer(new EnumDataTransformer(SafetyTestDate::class));
        $builder->get('safetyTestState')->addModelTransformer(new EnumDataTransformer(SafetyTestState::class));
        $builder->get('maintenanceState')->addModelTransformer(new EnumDataTransformer(MaintenanceState::class));

        $builder->get('isUsed')->addModelTransformer(new VehicleStatutDataTransformer());
        $builder->get('isImported')->addModelTransformer(new YesNoDataTransformer());
        $builder->get('isFirstHand')->addModelTransformer(new YesNoDataTransformer());

        $this->addAutocompletableCityField($builder, $data);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => VehicleSpecificsDTO::class,
            'translation_domain' => 'registration'
        ]);
    }


}
