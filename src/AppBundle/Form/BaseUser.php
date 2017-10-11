<?php


namespace AppBundle\Form;


use AppBundle\Form\Traits\HasPasswordTrait;
use Wamcar\User\Title;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BaseUser extends AbstractType
{
    use HasPasswordTrait;

    protected $isPasswordRequired = true;

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('email', EmailType::class)
            ->add('title', ChoiceType::class, [
                'expanded' => true,
                'multiple' => false,
                'choices' => Title::toArray(),
                'choice_label' => function ($value) {
                    return 'enum.title.' . strtolower($value);
                },
            ])
            ->add('name', TextType::class)
            ->add('phone', TextType::class, [
                'required' => false
            ])
            ->add('postalCode', TextType::class, [
                'required' => false
            ])
            ->add('city', TextType::class, [
                'required' => false
            ])
            ->add('newsletterOptin', CheckboxType::class, [
                'required' => false,
                'attr' => [
                    'class' => 'checkbox'
                ]
            ])

        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'label_format' => 'user.field.%name%.label',
        ));
    }

}
