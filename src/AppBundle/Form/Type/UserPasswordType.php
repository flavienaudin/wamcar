<?php


namespace AppBundle\Form\Type;


use AppBundle\Form\DTO\UserPasswordDTO;
use AppBundle\Form\Validator\Constraints\CorrectOldPassword;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserPasswordType extends AbstractType
{

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('oldPassword', PasswordType::class, [
                'required' => true,
                'error_bubbling' => false,
                'constraints' => [new CorrectOldPassword()]
            ])
            ->add('newPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'edit_user_data.password.repeat',
                'first_options' => [
                    'label_format' => 'user.field.password.first.label'
                ],
                'second_options' => [
                    'label_format' => 'user.field.password.second.label'
                ],
                'required' => true,
                'error_bubbling' => false,
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => UserPasswordDTO::class
        ]);
    }
}
