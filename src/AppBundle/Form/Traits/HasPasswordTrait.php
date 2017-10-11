<?php


namespace AppBundle\Form\Traits;


use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;

trait HasPasswordTrait
{
    public function addPassword(FormBuilderInterface $builder, $required = true, $firstPasswordLabel = 'user.field.password.first.label')
    {
        $builder->add('password', RepeatedType::class, [
            'type' => PasswordType::class,
            'invalid_message' => 'registration_data.password.repeat',
            'first_options' => [
                'label_format' => $firstPasswordLabel
            ],
            'second_options' => [
                'label_format' => 'user.field.password.second.label'
            ],
            'required' => $required
        ]);
    }
}
