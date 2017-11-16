<?php


namespace AppBundle\Form\Type;


use AppBundle\Form\DataTransformer\EnumDataTransformer;
use AppBundle\Form\DTO\UserInformationDTO;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Wamcar\User\Title;

class UserInformationType extends AbstractType
{

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('name', TextType::class)
            ->add('title', ChoiceType::class, [
                'expanded' => true,
                'multiple' => false,
                'choices' => Title::toArray(),
                'choice_label' => function ($value) {
                    return 'enum.title.' . strtolower($value);
                },
            ])
            ->add('phone', TextType::class, [
                'required' => false,
                'attr' => ['pattern' => '^((\+\d{1,3}(-| )?\(?\d\)?(-| )?\d{1,5})|(\(?\d{2,6}\)?))(-| )?(\d{3,4})(-| )?(\d{4})(( x| ext)\d{1,5}){0,1}$']
            ])
            ->add('oldPassword', PasswordType::class, [
                'required' => false,
                'error_bubbling' => true,
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
                'required' => false,
                'error_bubbling' => true,
            ]);

        $builder->get('title')->addModelTransformer(new EnumDataTransformer(Title::class));

    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => UserInformationDTO::class,
            'translation_domain' => 'user',
            'label_format' => 'user.field.%name%.label'
        ]);
    }
}
