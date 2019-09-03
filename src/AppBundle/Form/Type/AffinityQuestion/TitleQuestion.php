<?php


namespace AppBundle\Form\Type\AffinityQuestion;


use AppBundle\Form\DataTransformer\EnumDataTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
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
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        parent::buildView($view, $form, $options);
        $view->vars['label_trans'] = $options['label_trans'];
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults([
            'label' => 'affinity.question.title.label',
            'label_trans' => true,
            'required' => false,
            'expanded' => true,
            'multiple' => false,
            'choices' => Title::toArray(),
            'translation_domain' => 'messages'
        ]);
    }


}