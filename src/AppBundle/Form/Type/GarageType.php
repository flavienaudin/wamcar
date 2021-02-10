<?php


namespace AppBundle\Form\Type;


use AppBundle\Form\DTO\GarageDTO;
use AppBundle\Form\Type\Traits\AutocompleteableCityTrait;
use AppBundle\Form\Validator\Constraints\GarageAddressRequired;
use AppBundle\Form\Validator\Constraints\UniqueGarageSiren;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GarageType extends AbstractType
{
    use AutocompleteableCityTrait;

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('googlePlaceId', HiddenType::class)
            ->add('googleRating', HiddenType::class, ['required' => false]);
        if ($options['only_google_fields']) {
            $builder
                ->add('name', HiddenType::class, ['required' => false])
                ->add('openingHours', HiddenType::class, ['required' => false])
                ->add('address', HiddenType::class, ['required' => false])
                ->add('phone', HiddenType::class, [
                    'required' => false,
                    'attr' => ['pattern' => '^0\d{9}$']
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
                ->add('phone', TextType::class, [
                    'attr' => ['pattern' => '^0\d{8,9}$']
                ])
                ->add('banner', GaragePictureType::class, [
                    'error_bubbling' => true
                ])
                ->add('logo', GaragePictureType::class, [
                    'error_bubbling' => true
                ]);
        }


        $this->addAutocompletableCityField($builder, $builder->getData(), $options);
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
            'constraints' => [new UniqueGarageSiren(), new GarageAddressRequired()]
        ]);
    }
}
