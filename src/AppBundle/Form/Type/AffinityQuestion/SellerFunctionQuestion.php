<?php


namespace AppBundle\Form\Type\AffinityQuestion;


use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SellerFunctionQuestion extends ChoiceType
{
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
            'label' => 'affinity.question.seller_function.label',
            'label_trans' => true,
            'placeholder' => 'affinity.question.seller_function.placeholder',
            'translation_domain' => 'messages',
            'required' => false,
            'expanded' => false,
            'multiple' => false,
            'choices' => [
                'Mecano' => 'mecano',
                'Commercial' => 'seller',
                'Directeur' => 'director',
                'Chef VO' => 'vo_seller'
            ]
        ]);
    }
}