<?php


namespace AppBundle\Form\Type\AffinityQuestion;


use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContactMeansQuestion extends ChoiceType
{
    /**
     * @inheritDoc
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        parent::buildView($view, $form, $options);
        $view->vars['label_trans'] = $options['label_trans'] ?? null;
        $view->vars['list_class'] = $options['list_class'] ?? null;
        $view->vars['values_translation_domain'] = $options['values_translation_domain'] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults([
            'label' => "Lors d'un premier contact, vous préférez",
            'list_class' => 'small-up-4',
            'choices' => [
                'Messagerie sécurisée Wamcar' => 'messagerie',
                'Appel téléphonique' => 'phone_call',
                'SMS' => 'sms',
                'E-mail' => 'email'
            ],
            'required' => false,
            'expanded' => true,
            'multiple' => true
        ]);
    }


}