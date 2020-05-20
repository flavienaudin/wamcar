<?php


namespace AppBundle\Form\Type;


use AppBundle\Form\DTO\ProUserPresentationDTO;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;

class ProUserPresentationType extends UserPresentationType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder
            ->add('presentationTitle', TextType::class, [
                'required' => false,
                'constraints' => new Length(['max' => 50])
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults([
            'data_class' => ProUserPresentationDTO::class
        ]);
    }
}