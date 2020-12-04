<?php


namespace AppBundle\Form\Type;


use AppBundle\Form\DTO\ProContactMessageDTO;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

class ContactProType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstname', TextType::class, [
                'required' => true,
                'constraints' => [new NotBlank()]
            ])
            ->add('lastname', TextType::class, [
                'required' => false
            ])
            ->add('phonenumber', TextType::class, [
                'required' => false,
                'attr' => ['pattern' => '^0\d{9}$']
            ])
            ->add('email', EmailType::class, [
                'required' => true,
                'constraints' => [new NotBlank(), new Email()]
            ])
            ->add('message', TextareaType::class, [
                'required' => true,
                'constraints' => [new NotBlank()]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ProContactMessageDTO::class
        ]);
    }

}