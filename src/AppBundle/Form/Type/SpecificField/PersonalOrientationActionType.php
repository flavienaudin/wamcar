<?php

namespace AppBundle\Form\Type\SpecificField;


use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Wamcar\User\Enum\PersonalOrientationChoices;

class PersonalOrientationActionType extends ChoiceType
{

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('choices', PersonalOrientationChoices::toArray());
        $resolver->setDefault('expanded', true);
        $resolver->setDefault('multiple', false);
        $resolver->setDefault('choice_translation_domain', 'enumeration');
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return ChoiceType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'personal_orientation_action';
    }
}