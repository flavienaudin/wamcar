<?php


namespace AppBundle\Form\Type;


use AppBundle\Form\DTO\GarageDTO;
use AppBundle\Form\Type\Traits\AutocompleteableCityTrait;
use AppBundle\Form\Validator\Constraints\UniqueGarageSiren;
use Symfony\Component\Form\AbstractType;
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
        $data = $builder->getData();

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
            ->add('banner', GaragePictureType::class, [
                'error_bubbling' => true
            ])
            ->add('logo', GaragePictureType::class, [
                'error_bubbling' => true
            ]);

        $this->addAutocompletableCityField($builder, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => GarageDTO::class,
            'translation_domain' => 'garage',
            'label_format' => 'garage.field.%name%.label',
            'constraints' => new UniqueGarageSiren()
        ]);
    }
}
