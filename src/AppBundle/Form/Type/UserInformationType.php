<?php


namespace AppBundle\Form\Type;


use AppBundle\Form\DTO\UserInformationDTO;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
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

        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => UserInformationDTO::class,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'wamcar_edit_user';
    }
}
