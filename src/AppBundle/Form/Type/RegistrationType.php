<?php

namespace AppBundle\Form\Type;


use AppBundle\Form\DTO\RegistrationDTO;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
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
            ->add('email', EmailType::class, [
                'error_bubbling' => true,
                'attr' => [
                    'placeholder' => 'user.field.email.placeholder'
                ],
            ])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'registration_data.password.repeat',
                'first_options' => [
                    'label_format' => 'user.field.password.first.label',
                    'attr' => [
                        'placeholder' => 'user.field.password.first.placeholder'
                    ]
                ],
                'second_options' => [
                    'label_format' => 'user.field.password.second.label',
                    'attr' => [
                        'placeholder' => 'user.field.password.second.placeholder'
                    ]
                ],
                'required' => true,
                'error_bubbling' => true,
            ])
            ->add('accept', CheckboxType::class, [
                "mapped" => false,
            ]);
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
