<?php


namespace AppBundle\Form\Type\AffinityQuestion;


use AppBundle\Form\DataTransformer\EnumDataTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Wamcar\Affinity\Enum\ExpertiseFieldChoices;

class ExpertiseFieldsQuestion extends ChoiceType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder->addModelTransformer(new EnumDataTransformer(ExpertiseFieldChoices::class));
    }

    /**
     * @inheritDoc
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        parent::buildView($view, $form, $options);
        $view->vars['label_trans'] = $options['label_trans'];
        $view->vars['list_class'] = $options['list_class'];
        $view->vars['values_translation_domain'] = $options['values_translation_domain'];
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults([
            'label' => 'affinity.question.expertise_field.label',
            'label_trans' => true,
            'list_class' => 'small-up-4',
            'required' => true,
            'expanded' => true,
            'multiple' => true,
            'choices' => ExpertiseFieldChoices::toArray(),
            'values_translation_domain' => 'enumeration'
        ]);
    }



}