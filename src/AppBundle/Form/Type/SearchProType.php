<?php

namespace AppBundle\Form\Type;


use AppBundle\Form\Type\Traits\AutocompleteableCityTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Wamcar\User\ProService;
use Wamcar\User\ProServiceCategory;
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
            /*->add('text', TextType::class, [
                'required' => false
            ])*/
            ->add('radius', HiddenType::class, [
                'data' => 100,
                'error_bubbling' => true
            ])
            ->add('sorting', ChoiceType::class, [
                'choices' => DirectorySorting::toArray(),
                'choice_translation_domain' => 'enumeration',
                'required' => true,
                'empty_data' => DirectorySorting::DIRECTORY_SORTING_RELEVANCE
            ]);

        $mainFilters = $builder->getOption('mainFilters');
        /** @var ProService|null $querySelectedService */
        $querySelectedService = $builder->getOption('selectedService');
        foreach ($mainFilters as $filterPosition => $filterData) {
            /** @var ProServiceCategory $filterCategory */
            $filterCategory = $filterData['category'];
            $filterChoices = $filterData['services'];
            usort($filterChoices, function (ProService $a, ProService $b) {
                return strcmp($a->getName(), $b->getName());
            });
            $filterName = $filterCategory->getLabel();

            $options = [
                'class' => ProService::class,
                'mapped' => false,
                'label' => $filterName,
                'placeholder' => $filterName,
                'choice_label' => 'name',
                'choices' => $filterChoices,
                'required' => false,
                'attr' => []
            ];
            if($querySelectedService != null && $querySelectedService->getCategory() ===  $filterCategory){
              $options['data'] = new ArrayCollection([$querySelectedService]);
            }

            if ($filterCategory->isChoiceMultiple()) {
                $options['multiple'] = 'multiple';
                $options['attr']['multiple'] = 'multiple';
                $options['attr']['class'] = 'js-select2-input';
                $options['attr']['data-multiple'] = true;
                $options['attr']['data-placeholder'] = $filterName;
            }
            $builder->add(strtolower($filterName), EntityType::class, $options);
        }
        $this->addAutocompletableCityField($builder, $data);
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['mainFilters'] = $options['mainFilters'];
    }


    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'method' => 'POST',
            'mainFilters' => [],
            'selectedService' => null
        ]);
    }
}