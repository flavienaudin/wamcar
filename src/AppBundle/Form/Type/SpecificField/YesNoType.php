<?php

namespace AppBundle\Form\Type\SpecificField;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class YesNoType extends ChoiceType
{
    const YES = 'Oui';
    const NO = 'Non';

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('choices', [self::YES, self::NO]);
        $resolver->setDefault('expanded', true);
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
        return 'yesno';
    }
}
