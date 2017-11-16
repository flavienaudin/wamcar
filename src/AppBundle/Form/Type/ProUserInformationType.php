<?php


namespace AppBundle\Form\Type;


use AppBundle\Form\DTO\ProUserInformationDTO;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
                'attr' => ['pattern' => '^((\+\d{1,3}(-| )?\(?\d\)?(-| )?\d{1,5})|(\(?\d{2,6}\)?))(-| )?(\d{3,4})(-| )?(\d{4})(( x| ext)\d{1,5}){0,1}$']
            ])
            ->add('description', TextareaType::class, [
                'required' => false
            ])
        ;

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
