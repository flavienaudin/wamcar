<?php


namespace AppBundle\Form\Type;


use AppBundle\Form\DTO\ProUserInformationDTO;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProUserContactDetailsType extends UserContactDetailsType
{

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder
            ->add('phonePro', TextType::class, [
                'required' => false,
                'attr' => ['pattern' => '^0\d{9}$']
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ProUserInformationDTO::class
        ]);
    }
}
