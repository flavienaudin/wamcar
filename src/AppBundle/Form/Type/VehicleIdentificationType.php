<?php

namespace AppBundle\Form\Type;

use AppBundle\Form\DataTransformer\EnumDataTransformer;
use AppBundle\Form\DTO\VehicleIdentificationDTO;
use AppBundle\Form\DataTransformer\EnumToChoiceList;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\{
    ChoiceType
};
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Wamcar\Vehicle\Fuel;
use Wamcar\Vehicle\Enum\Transmission;

class VehicleIdentificationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // ToDo : Fill from elasticsearch when implementing dynamic fields
        $builder
            ->add('make', ChoiceType::class, [
                'choices' => ['Renault', 'VW'],
                'placeholder' => '--',
            ])
            ->add('model', ChoiceType::class, [
                'choices' => ['Twingo', 'Golf'],
                'placeholder' => '--',
            ])
            ->add('modelVersion', ChoiceType::class, [
                'choices' => [
                    'Twingo I (X06) (03/1993 - 08/1998)',
                    'Twingo II (X44) (07/2007 - 12/2011)',
                    'Twingo III (09/2014 - Aujourd\'hui)',
                    'Golf I (02/1974 - 12/1983)',
                    'Golf II (08/1983 - 07/1992)',
                    'Golf III (11/1991 - 12/1997)'
                ],
                'placeholder' => '--',
            ])
            ->add('engine', ChoiceType::class, [
                'choices' => [
                    '1.2 i 16V 75cv',
                    '1.2 i 16V 75cv  Emotion',
                    '1.2 i 16V 75cv  Expression',
                    '1.5 65 cv',
                    '1.5 65cv  GLS',
                    '1.8 GTI 112cv',
                    '1.8 GTI 112cv  GTI'
                ],
                'placeholder' => '--',
            ])
            ->add('transmission', ChoiceType::class, [
                'choices' => Transmission::toArray(),
            ])
            ->add('fuel', ChoiceType::class, [
                'choices' => [
                    'gasoline',
                    'diesel',
                    'lpg',
                    'hybrid',
                    'electric',
                ],
            ]);


        $builder->get('transmission')->addModelTransformer(new EnumDataTransformer(Transmission::class));
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => VehicleIdentificationDTO::class]);
    }


}
