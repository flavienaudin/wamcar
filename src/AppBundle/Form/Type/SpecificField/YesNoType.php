<?php

namespace AppBundle\Form\Type\SpecificField;

use AppBundle\Form\DataTransformer\YesNoDataTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class YesNoType extends AbstractType
{
    const YES = 'Oui';
    const NO = 'Non';

    /** @var YesNoDataTransformer */
    private $transformer;

    /**
     * YesNoType constructor.
     * @param YesNoDataTransformer $transformer
     */
    public function __construct(YesNoDataTransformer $transformer)
    {
        $this->transformer = $transformer;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer($this->transformer);
    }

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
