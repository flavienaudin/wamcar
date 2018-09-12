<?php

namespace AppBundle\Controller\Front\ProContext;


use AppBundle\Controller\Front\BaseController;
use AppBundle\Doctrine\Repository\DoctrineProUserRepository;
use AppBundle\Elasticsearch\Query\SearchResultProvider;
use AppBundle\Form\DTO\SearchProDTO;
use AppBundle\Form\Type\SearchProType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;

class DirectoryController extends BaseController
{

    /** @var FormFactoryInterface */
    protected $formFactory;
    /** @var SearchResultProvider */
    private $searchResultProvider;
    /** @var DoctrineProUserRepository */
    private $proUserRepository;

    /**
     * SearchController constructor.
     * @param FormFactoryInterface $formFactory
     * @param SearchResultProvider $searchResultProvider ,
     * @param DoctrineProUserRepository $proUserRepository
     */
    public function __construct(
        FormFactoryInterface $formFactory,
        SearchResultProvider $searchResultProvider,
        DoctrineProUserRepository $proUserRepository
    )
    {
        $this->formFactory = $formFactory;
        $this->searchResultProvider = $searchResultProvider;
        $this->proUserRepository = $proUserRepository;

    }

    public function viewAction(Request $request)
    {
        $searchVehicleDTO = new SearchProDTO();
        $searchProForm = $this->formFactory->create(SearchProType::class, $searchVehicleDTO, [
            'action' => $this->generateRoute('front_directory_view')
        ]);

        return $this->render('front/Directory/view.html.twig', [
            'searchProForm' => $searchProForm->createView(),
            'filterData' => (array)$searchProForm->getData(),
        ]);
    }
}