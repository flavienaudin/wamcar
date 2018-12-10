<?php

namespace AppBundle\Form\Type;


use AppBundle\Form\DataTransformer\EnumDataTransformer;
use AppBundle\Form\Type\SpecificField\PersonalOrientationActionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Wamcar\User\Enum\PersonalOrientationChoices;

class PersonalRegistrationOrientationType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('orientation', PersonalOrientationActionType::class, [
            'required' => true
        ]);

        $builder->get('orientation')->addModelTransformer(new EnumDataTransformer(PersonalOrientationChoices::class));
    }
}