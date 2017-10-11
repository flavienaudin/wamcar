<?php

namespace AppBundle\Form;


use AppBundle\DTO\Form\RegistrationData;
use AppBundle\Form\Traits\HasPasswordTrait;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Registration
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

        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => RegistrationData::class,
        ));
    }
}
