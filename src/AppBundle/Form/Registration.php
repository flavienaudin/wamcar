<?php

namespace AppBundle\Form;


use AppBundle\Form\DTO\RegistrationData;
use AppBundle\Form\Traits\HasPasswordTrait;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Registration extends AbstractType
{
    use HasPasswordTrait;

    protected $isPasswordRequired = true;

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->addPassword($builder, $this->isPasswordRequired);

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
