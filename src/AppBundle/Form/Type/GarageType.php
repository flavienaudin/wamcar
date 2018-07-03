<?php


namespace AppBundle\Form\Type;


use AppBundle\Form\DTO\GarageDTO;
use AppBundle\Form\Validator\Constraints\UniqueGarageSiren;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
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
            ->add('googlePlaceId', HiddenType::class)
            ->add('googleRating', HiddenType::class, ['required' => false])
            ->add('latitude', HiddenType::class, ['required' => false])
            ->add('longitude', HiddenType::class, ['required' => false]);
        if ($options['only_google_fields']) {
            $builder
                ->add('name', HiddenType::class, ['required' => false])
                ->add('openingHours', HiddenType::class, ['required' => false])
                ->add('address', HiddenType::class, ['required' => false])
                ->add('postalCode', HiddenType::class, ['required' => false])
                ->add('cityName', HiddenType::class, ['required' => false])
                ->add('phone', HiddenType::class, [
                    'required' => false,
                    'attr' => ['pattern' => '^((\+\d{1,3}(-| )?\(?\d\)?(-| )?\d{1,5})|(\(?\d{2,6}\)?))(-| )?(\d{3,4})(-| )?(\d{4})(( x| ext)\d{1,5}){0,1}$']
                ]);
        } else {
            $builder
                ->add('name', TextType::class)
                ->add('siren', TextType::class, [
                    'attr' => [
                        'pattern' => '^[0-9]{9}$',
                        'maxlength' => 9
                    ]
                ])
                ->add('presentation', TextareaType::class, [
                    'required' => false
                ])
                ->add('openingHours', TextareaType::class, [
                    'required' => false
                ])
                ->add('address', TextType::class)
                ->add('postalCode', TextType::class)
                ->add('cityName', TextType::class)
                ->add('phone', TextType::class, [
                    'attr' => ['pattern' => '^((\+\d{1,3}(-| )?\(?\d\)?(-| )?\d{1,5})|(\(?\d{2,6}\)?))(-| )?(\d{3,4})(-| )?(\d{4})(( x| ext)\d{1,5}){0,1}$']
                ])
                ->add('banner', GaragePictureType::class, [
                    'error_bubbling' => true
                ])
                ->add('logo', GaragePictureType::class, [
                    'error_bubbling' => true
                ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => GarageDTO::class,
            'only_google_fields' => false,
            'label_format' => 'garage.field.%name%.label',
            'constraints' => new UniqueGarageSiren()
        ]);
    }
}
