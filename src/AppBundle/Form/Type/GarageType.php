<?php


namespace AppBundle\Form\Type;


use AppBundle\Form\DataTransformer\EnumDataTransformer;
use AppBundle\Form\DTO\GarageDTO;
use AppBundle\Form\DTO\UserInformationDTO;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Wamcar\User\Title;

class GarageType extends AbstractType
{

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('name', TextType::class)
            ->add('siren', TextType::class)
            ->add('phone', TextType::class)
            ->add('email', EmailType::class)
            ->add('openingHours', TextareaType::class)
            ->add('presentation', TextareaType::class)
            ->add('benefit', TextareaType::class)
            ->add('address', TextType::class)
            ->add('postalCode', TextType::class)
            ->add('cityName', TextType::class)
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => GarageDTO::class,
            'translation_domain' => 'garage',
            'label_format' => 'garage.field.%name%.label'
        ]);
    }
}
