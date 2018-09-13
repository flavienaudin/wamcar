<?php

namespace AppBundle\Controller\Front\ProContext;


use AppBundle\Controller\Front\BaseController;
use AppBundle\Elasticsearch\Query\SearchResultProvider;
use AppBundle\Form\DTO\SearchProDTO;
use AppBundle\Form\Type\SearchProType;
use AppBundle\Services\User\UserEditionService;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;

class DirectoryController extends BaseController
{

    /** @var FormFactoryInterface */
    protected $formFactory;
    /** @var SearchResultProvider */
    private $searchResultProvider;
    /** @var UserEditionService $userEditionService */
    private $userEditionService;

    /**
     * DirectoryController constructor.
     * @param FormFactoryInterface $formFactory
     * @param SearchResultProvider $searchResultProvider ,
     * @param UserEditionService $userEditionService
     */
    public function __construct(
        FormFactoryInterface $formFactory,
        SearchResultProvider $searchResultProvider,
        UserEditionService $userEditionService
    )
    {
        $this->formFactory = $formFactory;
        $this->searchResultProvider = $searchResultProvider;
        $this->userEditionService = $userEditionService;
    }

    public function viewAction(Request $request, int $page = 1)
    {
        $searchProDTO = new SearchProDTO();
        $searchProForm = $this->formFactory->create(SearchProType::class, $searchProDTO, [
            'action' => $this->generateRoute('front_directory_view')
        ]);
        $searchProForm->handleRequest($request);
        $result = $this->searchResultProvider->getQueryDirectoryProUserResult($searchProDTO, $page);

        $proUserResult = $this->userEditionService->getUsersBySearchResult($result);

        return $this->render('front/Directory/view.html.twig', [
            'searchProForm' => $searchProForm->createView(),
            'result' => $proUserResult,
            'filterData' => (array)$searchProForm->getData(),
            'page' => $page,
            'lastPage' => $result->numberOfPages()
        ]);
    }
}