<?php

namespace AppBundle\Form\Type;


use AppBundle\Form\DTO\SearchProDTO;
use AppBundle\Form\Type\Traits\AutocompleteableCityTrait;
use AppBundle\Utils\RadiusChoice;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Wamcar\Vehicle\Enum\DirectorySorting;

class SearchProType extends AbstractType
{
    use AutocompleteableCityTrait;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $data = $builder->getData();
        $builder
            ->add('text', TextType::class, [
                'required' => false
            ])
            ->add('radius', ChoiceType::class, [
                'choices' => RadiusChoice::getListRadius(),
                'data' => 50,
                'error_bubbling' => true,
            ])
            ->add('sorting', ChoiceType::class, [
                'choices' => DirectorySorting::toArray(),
                'choice_translation_domain' => 'enumeration',
                'required' => true,
                'empty_data' => DirectorySorting::DIRECTORY_SORTING_RELEVANCE
            ]);
        $this->addAutocompletableCityField($builder, $data);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'data_class' => SearchProDTO::class,
            'method' => 'POST'
        ]);
    }
}