<?php


namespace AppBundle\Form\Type;


use AppBundle\Form\DTO\GarageDTO;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GarageType extends AbstractType
{

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('name', TextType::class)
            ->add('siren', TextType::class, [
                'attr' => [
                    'pattern' => '^[0-9]{9}$',
                    'maxlength' => 9
                ]
            ])
            ->add('openingHours', TextareaType::class, [
                'required' => false
            ])
            ->add('presentation', TextareaType::class, [
                'required' => false
            ])
            ->add('address', TextType::class)
            ->add('phone', TextType::class, [
                'attr' => ['pattern' => '^((\+\d{1,3}(-| )?\(?\d\)?(-| )?\d{1,5})|(\(?\d{2,6}\)?))(-| )?(\d{3,4})(-| )?(\d{4})(( x| ext)\d{1,5}){0,1}$']
            ])
            ->add('postalCode', TextType::class, [
                'attr' => [
                    'pattern' => '^[0-9][0-9|A|B][0-9]{3}$'
                ]
            ])
            ->add('cityName', TextType::class)
            ->add('latitude', HiddenType::class)
            ->add('longitude', HiddenType::class)
            ->add('banner', GaragePictureType::class, [
                'error_bubbling' => true
            ])
            ->add('logo', GaragePictureType::class, [
                'error_bubbling' => true
            ])
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
