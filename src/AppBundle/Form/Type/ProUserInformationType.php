<?php


namespace AppBundle\Form\Type;


use AppBundle\Form\DTO\ProUserInformationDTO;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;

class ProUserInformationType extends UserInformationType
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
            ])
            ->add('presentationTitle', TextType::class, [
                'required' => false,
                'constraints' => new Length(['max' => 50])
            ])
            ->add('banner', UserPictureType::class, [
                'error_bubbling' => true
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ProUserInformationDTO::class,
            'translation_domain' => 'user',
            'label_format' => 'user.field.%name%.label'
        ]);
    }
}
