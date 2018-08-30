<?php

namespace AppBundle\Form\Type;


use AppBundle\Form\DataTransformer\EnumDataTransformer;
use AppBundle\Form\DTO\UserPreferencesDTO;
use AppBundle\Form\Type\SpecificField\SwitchType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Wamcar\Vehicle\Enum\NotificationFrequency;

class UserPreferencesType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('privateMessageEmailEnabled', SwitchType::class)
            ->add('privateMessageEmailFrequency', ChoiceType::class, [
                'multiple' => false,
                'choices' => NotificationFrequency::toArray(),
                'choice_translation_domain' => 'enumeration'
            ])
            ->add('likeEmailEnabled', SwitchType::class)
            ->add('likeEmailFrequency', ChoiceType::class, [
                'multiple' => false,
                'choices' => NotificationFrequency::toArray(),
                'choice_translation_domain' => 'enumeration'
            ]);
        $builder->get('privateMessageEmailFrequency')->addModelTransformer(new EnumDataTransformer(NotificationFrequency::class, NotificationFrequency::ONCE_A_DAY()));
        $builder->get('likeEmailFrequency')->addModelTransformer(new EnumDataTransformer(NotificationFrequency::class, NotificationFrequency::ONCE_A_DAY()));
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefault('data_class', UserPreferencesDTO::class);
    }


}