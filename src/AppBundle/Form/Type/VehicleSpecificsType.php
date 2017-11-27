<?php

namespace AppBundle\Form\Type;

use AppBundle\Form\DataTransformer\EnumDataTransformer;
use AppBundle\Form\DataTransformer\YesNoDataTransformer;
use AppBundle\Form\DTO\VehicleSpecificsDTO;
use AppBundle\Form\Type\SpecificField\StarType;
use AppBundle\Form\Type\SpecificField\YesNoType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\{
    ChoiceType, DateType, HiddenType, IntegerType, TextareaType, TextType
};
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Wamcar\Vehicle\Enum\MaintenanceState;
use Wamcar\Vehicle\Enum\SafetyTestDate;
use Wamcar\Vehicle\Enum\SafetyTestState;

class VehicleSpecificsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
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
            ->add('mileage', IntegerType::class, [
                'error_bubbling' => true,
            ])
            ->add('isTimingBeltChanged', YesNoType::class, [
                'error_bubbling' => true,
            ])
            ->add('safetyTestDate', ChoiceType::class, [
                'choices' => SafetyTestDate::toArray(),
                'error_bubbling' => true,
            ])
            ->add('safetyTestState', ChoiceType::class, [
                'choices' => SafetyTestState::toArray(),
                'error_bubbling' => true,
            ])
            ->add('bodyState', StarType::class, [
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
                'choices' => MaintenanceState::toArray(),
                'error_bubbling' => true,
            ])
            ->add('isImported', YesNoType::class, [
                'error_bubbling' => true,
            ])
            ->add('isFirstHand', YesNoType::class, [
                'error_bubbling' => true,
            ])
            ->add('additionalInformation', TextareaType::class, [
                'required' => false,
                'error_bubbling' => true,
            ])
            ->add('postalCode', TextType::class, [
                'attr' => [
                    'pattern' => '^[0-9][0-9|A|B][0-9]{3}$'
                ]
            ])
            ->add('cityName', TextType::class)
            ->add('latitude', HiddenType::class)
            ->add('longitude', HiddenType::class)
        ;

        $builder->get('safetyTestDate')->addModelTransformer(new EnumDataTransformer(SafetyTestDate::class));
        $builder->get('safetyTestState')->addModelTransformer(new EnumDataTransformer(SafetyTestState::class));
        $builder->get('maintenanceState')->addModelTransformer(new EnumDataTransformer(MaintenanceState::class));

        $builder->get('isTimingBeltChanged')->addModelTransformer(new YesNoDataTransformer());
        $builder->get('isImported')->addModelTransformer(new YesNoDataTransformer());
        $builder->get('isFirstHand')->addModelTransformer(new YesNoDataTransformer());
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
