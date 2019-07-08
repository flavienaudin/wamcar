<?php

namespace AppBundle\Form\Type;


use AppBundle\Form\DataTransformer\EnumDataTransformer;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Wamcar\Vehicle\Enum\LeadCriteriaSelection;

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
            ->add('leadLocalizationRadiusCriteria', IntegerType::class, [
                'required' => true,
                'attr' => [
                    'min' => 20
                ]
            ])
            ->add('leadPartExchangeSelectionCriteria', ChoiceType::class, [
                'multiple' => false,
                'choices' => LeadCriteriaSelection::toArray(),
                'choice_translation_domain' => 'enumeration'
            ])
            ->add('leadPartExchangeKmMaxCriteria', IntegerType::class, [
                'required' => false,
                'attr' => [
                    'min' => 0
                ]
            ])
            ->add('leadProjectSelectionCriteria', ChoiceType::class, [
                'multiple' => false,
                'choices' => LeadCriteriaSelection::toArray(),
                'choice_translation_domain' => 'enumeration'
            ])
            ->add('leadProjectBudgetMinCriteria', IntegerType::class, [
                'required' => false,
                'attr' => [
                    'min' => 0
                ]
            ]);

        $builder->get('leadPartExchangeSelectionCriteria')->addModelTransformer(new EnumDataTransformer(LeadCriteriaSelection::class, LeadCriteriaSelection::LEAD_CRITERIA_NO_MATTER()));
        $builder->get('leadProjectSelectionCriteria')->addModelTransformer(new EnumDataTransformer(LeadCriteriaSelection::class, LeadCriteriaSelection::LEAD_CRITERIA_NO_MATTER()));
    }
}