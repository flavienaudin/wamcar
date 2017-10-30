<?php


namespace AppBundle\Form\Type;


use AppBundle\Form\DTO\PasswordLostDTO;
use AppBundle\Form\DTO\PasswordResetDTO;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PasswordResetType extends AbstractType
{

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
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
            ]);


    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'security',
            'label_format' => 'user.field.%name%.label'
        ]);
    }
}
