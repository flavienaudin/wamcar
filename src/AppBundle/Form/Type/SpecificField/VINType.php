<?php

namespace AppBundle\Form\Type\SpecificField;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Regex;

class VINType extends AbstractType
{


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'attr' => [
                'pattern' => '^[A-HJ-NPR-Za-hj-npr-z\d]{17}$',
                'maxlength' => 17
            ],
            'constraints' => new Regex(['pattern' => '/^[A-HJ-NPR-Z\d]{17}$/i'])
        ]);
    }

    public function getParent()
    {
        return TextType::class;
    }
}