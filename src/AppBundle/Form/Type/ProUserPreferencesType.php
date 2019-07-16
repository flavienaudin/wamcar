<?php

namespace AppBundle\Form\Type;


use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;

class ProUserPreferencesType extends UserPreferencesType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder
            ->add('leadEmailEnabled', CheckboxType::class, [
                'required' => false
            ])
            ->add('leadOnlyPartExchange', CheckboxType::class, [
                'required' => false
            ])
            ->add('leadOnlyProject', CheckboxType::class, [
                'required' => false
            ])
            ->add('leadProjectWithPartExchange', CheckboxType::class, [
                'required' => false
            ])
            ->add('leadLocalizationRadiusCriteria', IntegerType::class, [
                'required' => true,
                'attr' => [
                    'min' => 20
                ]
            ])
            ->add('leadPartExchangeKmMaxCriteria', IntegerType::class, [
                'required' => false,
                'attr' => [
                    'min' => 0
                ]
            ])
            ->add('leadProjectBudgetMinCriteria', IntegerType::class, [
                'required' => false,
                'attr' => [
                    'min' => 0
                ]
            ]);
    }
}