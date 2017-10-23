<?php

namespace AppBundle\Form\Type;

use AppBundle\Form\DataTransformer\EnumDataTransformer;
use AppBundle\Form\DTO\VehicleIdentificationDTO;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\{
    ChoiceType
};
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Wamcar\Vehicle\Enum\Transmission;

class VehicleIdentificationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $availableValues = $options['available_values'] ?? [];

        $builder
            ->add('make', ChoiceType::class, [
                'choices' => $availableValues['make'] ?? [],
                'placeholder' => count($availableValues['make'] ?? []) === 1 ? false : '',
            ])
            ->add('model', ChoiceType::class, [
                'choices' => $availableValues['model'] ?? [],
                'placeholder' => count($availableValues['model'] ?? []) === 1 ? false : '',
            ]);


        if (!$options['small_version']) {
            $builder
                ->add('modelVersion', ChoiceType::class, [
                    'choices' => $availableValues['modelVersion'] ?? [],
                    'placeholder' => count($availableValues['modelVersion'] ?? []) === 1 ? false : '',
                ])
                ->add('engine', ChoiceType::class, [
                    'choices' => $availableValues['engine'] ?? [],
                    'placeholder' => count($availableValues['engine'] ?? []) === 1 ? false : '',
                ])
                ->add('transmission', ChoiceType::class, [
                    'choices' => Transmission::toArray(),
                ])
                ->add('fuel', ChoiceType::class, [
                    'choices' => $availableValues['fuel'] ?? [],
                    'placeholder' => count($availableValues['fuel'] ?? []) === 1 ? false : '',
                ]);

            $builder->get('transmission')->addModelTransformer(new EnumDataTransformer(Transmission::class));
        }
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('available_values');
        $resolver->setRequired('small_version');

        $resolver->setDefaults([
            'data_class' => VehicleIdentificationDTO::class,
            'small_version' => false
        ]);
    }


}
