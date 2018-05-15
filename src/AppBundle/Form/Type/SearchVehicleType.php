<?php

namespace AppBundle\Form\Type;

use AppBundle\Form\DTO\SearchVehicleDTO;
use AppBundle\Form\Type\Traits\AutocompleteableCityTrait;
use AppBundle\Utils\BudgetChoice;
use AppBundle\Utils\MileageChoice;
use AppBundle\Utils\YearsChoice;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Wamcar\Vehicle\Enum\Transmission;

class SearchVehicleType extends AbstractType
{
    use AutocompleteableCityTrait;

    /** @var array */
    private $preferredMakes;

    public function __construct($preferredMakes = [])
    {
        $this->preferredMakes = $preferredMakes;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $data = $builder->getData();
        $availableValues = $options['available_values'] ?? [];
        $smallVersion = $options['small_version'] ?? [];

        $builder->add('text', TextType::class, [
            'required' => false
        ]);

        if (!$smallVersion) {
            $builder
                ->add('make', ChoiceType::class, [
                    'choices' => $availableValues['make'] ?? [],
                    'preferred_choices' => function ($val, $key) {
                        return in_array($key, $this->preferredMakes);
                    },
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
                    'choices' => MileageChoice::getMileageMax(),
                    'error_bubbling' => true,
                ])
                ->add('yearsMin', ChoiceType::class, [
                    'choices' => YearsChoice::getLastYears(58),
                    'error_bubbling' => true,
                ])
                ->add('yearsMax', ChoiceType::class, [
                    'choices' => YearsChoice::getLastYears(58),
                    'error_bubbling' => true,
                ])
                ->add('budgetMin', ChoiceType::class, [
                    'choices' => BudgetChoice::getListMin(),
                    'error_bubbling' => true,
                ])
                ->add('budgetMax', ChoiceType::class, [
                    'choices' => BudgetChoice::getListMax(),
                    'error_bubbling' => true,
                ]);

            $this->addAutocompletableCityField($builder, $data);
        }
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SearchVehicleDTO::class,
            'translation_domain' => 'search',
            'csrf_protection' => false,
            'available_values' => [],
            'small_version' => false,
            'method' => 'GET'
        ]);
        $resolver->setRequired('available_values');
    }
}
