<?php

namespace AppBundle\Form\Type;


use AppBundle\Form\Type\SpecificField\PersonalOrientationActionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class PersonalRegistrationOrientationType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('action', PersonalOrientationActionType::class);
    }
}