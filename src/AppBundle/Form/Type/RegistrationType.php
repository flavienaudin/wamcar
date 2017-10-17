<?php

namespace AppBundle\Form\Type;


use AppBundle\Form\DTO\RegistrationDTO;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RegistrationType extends AbstractType
{

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class)
            ->add('password', RepeatedType::class, [
            'type' => PasswordType::class,
            'invalid_message' => 'registration_data.password.repeat',
            'first_options' => [
                'label_format' => 'user.field.password.first.label'
            ],
            'second_options' => [
                'label_format' => 'user.field.password.second.label'
            ],
            'required' => true
        ]);
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => RegistrationDTO::class,
            'translation_domain' => 'security',
            'label_format' => 'user.field.%name%.label',
        ));
    }
}
