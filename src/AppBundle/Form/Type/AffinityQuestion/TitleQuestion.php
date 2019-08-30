<?php


namespace AppBundle\Form\Type\AffinityQuestion;


use AppBundle\Form\DataTransformer\EnumDataTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Wamcar\User\Title;

class TitleQuestion extends ChoiceType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder->addModelTransformer(new EnumDataTransformer(Title::class));
    }


    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults([
            'label' => 'affinity.question.title.label',
            'expanded' => true,
            'multiple' => false,
            'choices' => Title::toArray(),
            'translation_domain' => 'messages'
        ]);
    }


}