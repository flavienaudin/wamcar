<?php

namespace AppBundle\Form\Type;

use AppBundle\Form\DataTransformer\EnumDataTransformer;
use AppBundle\Form\DTO\VehicleInformationDTO;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\{
    ChoiceType, TextType
};
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Wamcar\Vehicle\Enum\Transmission;

class VehicleInformationType extends AbstractType
{
    /** @var array */
    private $preferredMakes;

    public function __construct($preferredMakes = [])
    {
        $this->preferredMakes = $preferredMakes;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $availableValues = $options['available_values'] ?? [];
        $builder
            ->add('make', ChoiceType::class, [
                'choices' => $availableValues['make'] ?? [],
                'preferred_choices' => function($val, $key) {
                    return in_array($key, $this->preferredMakes);
                },
                'placeholder' => count($availableValues['make'] ?? []) === 1 ? false : 'vehicle.field.make.placeholder',
                'translation_domain' => 'messages',
                'error_bubbling' => true,
            ])
            ->add('model', ChoiceType::class, [
                'choices' => $availableValues['model'] ?? [],
                'placeholder' => count($availableValues['model'] ?? []) === 1 ? false : 'vehicle.field.model.placeholder',
                'translation_domain' => 'messages',
                'error_bubbling' => true
            ]);


        if (!$options['small_version']) {
            $builder
                ->add('engine', ChoiceType::class, [
                    'choices' => $availableValues['engine'] ?? [],
                    'placeholder' => count($availableValues['engine'] ?? []) === 1 ? false : 'vehicle.field.engine.placeholder',
                    'translation_domain' => 'messages',
                    'error_bubbling' => true,
                ])
                ->add('transmission', ChoiceType::class, [
                    'choices' => Transmission::toArray(),
                    'error_bubbling' => true,
                    'translation_domain' => 'enumeration',
                ])
                ->add('fuel', ChoiceType::class, [
                    'choices' => $availableValues['fuel'] ?? [],
                    'placeholder' => count($availableValues['fuel'] ?? []) === 1 ? false : 'vehicle.field.fuel.placeholder',
                    'translation_domain' => 'messages',
                    'error_bubbling' => true,
                ]);

            $builder->get('transmission')->addModelTransformer(new EnumDataTransformer(Transmission::class, Transmission::TRANSMISSION_MANUAL()));

            // Replace choice by text to prevent validation, as fields are dynamically filled
            $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
                $form = $event->getForm();

                foreach (['make', 'model', 'engine', 'fuel'] as $field) {
                    if ($form->has($field)) {
                        $form->remove($field);
                        $form->add($field, TextType::class);
                    }
                }


            });
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
            'data_class' => VehicleInformationDTO::class,
            'small_version' => false,
            'translation_domain' => 'registration'
        ]);
    }


}
