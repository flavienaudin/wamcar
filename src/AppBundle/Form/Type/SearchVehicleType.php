<?php

namespace AppBundle\Form\Type;

use AppBundle\Form\DTO\SearchVehicleDTO;
use AppBundle\Utils\YearsChoice;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Wamcar\Vehicle\Enum\Transmission;

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
            ->add('make', ChoiceType::class, [
                'choices' => $availableValues['make'] ?? [],
                'placeholder' => count($availableValues['make'] ?? []) === 1 ? false : '',
                'error_bubbling' => true,
            ])
            ->add('model', ChoiceType::class, [
                'choices' => $availableValues['model'] ?? [],
                'placeholder' => count($availableValues['model'] ?? []) === 1 ? false : '',
                'error_bubbling' => true,
            ])
            ->add('transmission', ChoiceType::class, [
                'choices' => Transmission::toArray(),
                'error_bubbling' => true,
            ])
            ->add('fuel', ChoiceType::class, [
                'choices' => $availableValues['fuel'] ?? [],
                'placeholder' => count($availableValues['fuel'] ?? []) === 1 ? false : '',
                'error_bubbling' => true,
            ])
            ->add('mileageMax', ChoiceType::class, [
                'choices' => [
                    '50 000 Km' => '50000',
                    '100 000 Km' => '100000',
                    '150 000 Km' => '150000',
                    '200 000 Km' => '200000'
                ],
                'error_bubbling' => true,
            ])
            ->add('yearsMin', ChoiceType::class, [
                'choices' => YearsChoice::getLastYears(),
                'error_bubbling' => true,
            ])
            ->add('yearsMax', ChoiceType::class, [
                'choices' => YearsChoice::getLastYears(),
                'error_bubbling' => true,
            ])
            ->add('budgetMin', ChoiceType::class, [
                'choices' => [
                    '10 000 €' => '10000',
                    '20 000 €' => '20000',
                    '30 000 €' => '30000'
                ],
                'error_bubbling' => true,
            ])
            ->add('budgetMax', ChoiceType::class, [
                'choices' => [
                    '10 000 €' => '10000',
                    '20 000 €' => '20000',
                    '30 000 €' => '30000'
                ],
                'error_bubbling' => true,
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
