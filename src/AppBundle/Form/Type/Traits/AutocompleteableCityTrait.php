<?php


namespace AppBundle\Form\Type\Traits;


use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

trait AutocompleteableCityTrait
{

    protected function addAutocompletableCityField(FormBuilderInterface $builder, $data)
    {
        $builder
            ->add('postalCode', ChoiceType::class, [
                'choices' => $data && $data->postalCode ? [$data->cityName . ' ('.$data->postalCode.')' => $data->postalCode] : null,
                'attr' => [
                    'class' => 'js-city-autocomplete'
                ]
            ])
            ->add('cityName', HiddenType::class, [
                'required' => false,
                'attr' => [
                    'class' => 'js-cityname'
                ]
            ])
            ->add('latitude', HiddenType::class, [
                'required' => false,
                'attr' => [
                    'class' => 'js-latitude'
                ]
            ])
            ->add('longitude', HiddenType::class, [
                'required' => false,
                'attr' => [
                    'class' => 'js-longitude'
                ]
            ]);

        // Replace choice by text to prevent validation, as fields are dynamically filled
        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event)
        {
            $form = $event->getForm();
            $data = $event->getData();

            if ($form->has('postalCode')) {
                $form->remove('postalCode');
                $form->add('postalCode', ChoiceType::class, [
                    'choices' => $data && $data['postalCode'] ? [$data['cityName'] . ' ('.$data['postalCode'].')' => $data['postalCode']] : null,
                    'attr' => [
                        'class' => 'js-city-autocomplete'
                    ]
                ]);
            }
        });
    }
}
