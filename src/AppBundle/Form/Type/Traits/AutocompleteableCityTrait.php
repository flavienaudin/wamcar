<?php


namespace AppBundle\Form\Type\Traits;


use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

trait AutocompleteableCityTrait
{

    protected function addAutocompletableCityField(FormBuilderInterface $builder, $data, $options = [])
    {
        if (isset($options['only_google_fields']) && $options['only_google_fields']) {
            $builder->add('postalCode', HiddenType::class, [
                'required' => false,
            ]);
        } else {
            $builder->add('postalCode', ChoiceType::class, [
                'choices' => $data && $data->postalCode ? [$data->cityName . ' (' . $data->postalCode . ')' => $data->postalCode] : null,
            ]);
        }
        $builder
            ->add('cityName', HiddenType::class, [
                'required' => false
            ])
            ->add('latitude', HiddenType::class, [
                'required' => false
            ])
            ->add('longitude', HiddenType::class, [
                'required' => false
            ]);

        // Replace choice by text to prevent validation, as fields are dynamically filled
        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $form = $event->getForm();
            $data = $event->getData();

            if ($form->has('postalCode')) {
                $form->remove('postalCode');
                $form->add('postalCode', ChoiceType::class, [
                    'choices' => $data && isset($data['postalCode']) ? [$data['cityName'] . ' (' . $data['postalCode'] . ')' => $data['postalCode']] : null,
                    'attr' => [
                        'class' => 'js-city-autocomplete'
                    ]
                ]);
            }
        });
    }
}
