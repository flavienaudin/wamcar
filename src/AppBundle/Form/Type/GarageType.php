<?php


namespace AppBundle\Form\Type;


use AppBundle\Form\DTO\GarageDTO;
use Symfony\Component\Form\AbstractType;
use AppBundle\Form\DataTransformer\EnumDataTransformer;
use AppBundle\Form\DTO\UserInformationDTO;
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
            ->add('openingHours', TextareaType::class, [
                'required' => false
            ])
            ->add('presentation', TextareaType::class, [
                'required' => false
            ])
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
