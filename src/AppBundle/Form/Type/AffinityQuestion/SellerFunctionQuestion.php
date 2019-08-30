<?php


namespace AppBundle\Form\Type\AffinityQuestion;


use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SellerFunctionQuestion extends ChoiceType
{

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults([
            'label' => 'affinity.question.seller_function.label',
            'expanded' => true,
            'multiple' => true,
            'choices' => [
                'Mecano' => 'mecano',
                'Commercial' => 'seller',
                'Directeur' => 'director',
                'Chef VO' => 'vo_seller'
            ]
        ]);
    }
}