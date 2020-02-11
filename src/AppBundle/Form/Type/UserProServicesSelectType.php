<?php


namespace AppBundle\Form\Type;


use AppBundle\Form\Type\SpecificField\ProServiceCategoryServicesSelectType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Wamcar\User\ProService;
use Wamcar\User\ProServiceCategory;
use Wamcar\User\ProServiceCategoryRepository;

class UserProServicesSelectType extends AbstractType
{

    /** @var ProServiceCategoryRepository */
    private $proServiceCategoryRepository;

    /**
     * UserProServicesSelectType constructor.
     * @param ProServiceCategoryRepository $proServiceCategoryRepository
     */
    public function __construct(ProServiceCategoryRepository $proServiceCategoryRepository)
    {
        $this->proServiceCategoryRepository = $proServiceCategoryRepository;
    }


    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $formEvent) use ($options) {
            $form = $formEvent->getForm();
            $userProServicesByCategory = $formEvent->getData();

            /** @var ProServiceCategory $category */
            foreach ($options['categories'] as $category) {
                $fieldName = SearchProType::getCategoryFieldName($category);
                $dataByCategory = [];
                if (isset($userProServicesByCategory[$category->getLabel()])) {

                    $userProServicesByCategory[$fieldName] = $userProServicesByCategory[$category->getLabel()];
                    unset($userProServicesByCategory[$category->getLabel()]);

                    /** @var ProService $proService */
                    foreach ($userProServicesByCategory[$fieldName] as $proService) {
                        $dataByCategory[$proService->getSlug()] = $proService;
                    }
                }

                $form
                    ->add($fieldName, ProServiceCategoryServicesSelectType::class, [
                        'label' => $category->getLabel(),
                        'choices' => $category->getProServices(),
                        'class' => ProService::class,
                        'data' => $dataByCategory,
                        'category_description' => $category->getDescription()
                    ]);
            }

            $formEvent->setData($userProServicesByCategory);
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $categories = $this->proServiceCategoryRepository->findEnabledOrdered();
        $resolver->setDefault('categories', $categories);
    }
}