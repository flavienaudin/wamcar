<?php

namespace AppBundle\Form\Type;


use AppBundle\Form\DTO\RegistrationDTO;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

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
            ->add('firstName', TextType::class, [
                'required' => true,
                'constraints' => new NotBlank()
            ])
            ->add('lastName', TextType::class)
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
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
        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $formEvent) {
            /** @var RegistrationDTO $data */
            $data = $formEvent->getData();
            if (!empty($data->target_path)) {
                $form = $formEvent->getForm();
                $form->add('target_path', HiddenType::class, [
                    'required' => false,
                    'label' => false,
                    'label_attr' => ['class' => 'show-for-sr']
                ]);
            }
        });
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
