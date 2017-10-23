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
                'choices' => $availableValues['makes'] ?? [],
                'placeholder' => count($availableValues['makes'] ?? []) === 1 ? false : '--',
            ])
            ->add('model', ChoiceType::class, [
                'choices' => $availableValues['models'] ?? [],
                'placeholder' => count($availableValues['models'] ?? []) === 1 ? false : '--',
            ]);


        if (!$options['small_version']) {
            $builder
                ->add('modelVersion', ChoiceType::class, [
                    'choices' => $availableValues['modelVersions'] ?? [],
                    'placeholder' => count($availableValues['modelVersions'] ?? []) === 1 ? false : '--',
                ])
                ->add('engine', ChoiceType::class, [
                    'choices' => $availableValues['engines'] ?? [],
                    'placeholder' => count($availableValues['engines'] ?? []) === 1 ? false : '--',
                ])
                ->add('transmission', ChoiceType::class, [
                    'choices' => Transmission::toArray(),
                ])
                ->add('fuel', ChoiceType::class, [
                    'choices' => $availableValues['fuels'] ?? [],
                    'placeholder' => count($availableValues['fuels'] ?? []) === 1 ? false : '--',
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
