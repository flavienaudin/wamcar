<?php

namespace AppBundle\Form\Type;


use AppBundle\Form\DTO\SearchProDTO;
use AppBundle\Form\Type\Traits\AutocompleteableCityTrait;
use AppBundle\Utils\RadiusChoice;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Wamcar\User\ProService;
use Wamcar\User\ProServiceRepository;
use Wamcar\Vehicle\Enum\DirectorySorting;

class SearchProType extends AbstractType
{

    use AutocompleteableCityTrait;

    /** @var ProServiceRepository */
    private $proServiceRepository;

    /**
     * SearchProType constructor.
     * @param ProServiceRepository $proServiceRepository
     */
    public function __construct(ProServiceRepository $proServiceRepository)
    {
        $this->proServiceRepository = $proServiceRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $data = $builder->getData();
        $builder
            ->add('text', TextType::class, [
                'required' => false
            ])
            ->add('service', EntityType::class, [
                'class' => ProService::class,
                'choice_label' => 'name',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('s')->orderBy('s.name', 'ASC');
                },
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