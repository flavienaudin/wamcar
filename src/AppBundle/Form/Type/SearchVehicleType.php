<?php

namespace AppBundle\Form\Type;

use AppBundle\Form\DTO\SearchVehicleDTO;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchVehicleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('text', TextType::class, [
                'required' => false
            ])
            ->add('postalCode', TextType::class, [
                'attr' => [
                    'pattern' => '^[0-9][0-9|A|B][0-9]{3}$'
                ]
            ])
            ->add('cityName', TextType::class, [
                'required' => false
            ])
            ->add('latitude', HiddenType::class, [
                'required' => false
            ])
            ->add('longitude', HiddenType::class, [
                'required' => false
            ])
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SearchVehicleDTO::class,
            'translation_domain' => 'search'
        ]);
    }
}
