<?php

namespace AppBundle\Controller\Front\ProContext;

use AppBundle\Controller\Front\BaseController;
use AppBundle\Elasticsearch\Query\SearchResultProvider;
use AppBundle\Form\DTO\SearchVehicleDTO;
use AppBundle\Form\Type\SearchVehicleType;
use AppBundle\Services\User\UserEditionService;
use AppBundle\Services\Vehicle\PersonalVehicleEditionService;
use AppBundle\Services\Vehicle\ProVehicleEditionService;
use AppBundle\Utils\VehicleInfoAggregator;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Wamcar\User\ProUser;

class SearchController extends BaseController
{
    const TAB_ALL = 'TAB_ALL';
    const TAB_PERSONAL = 'TAB_PERSONAL';
    const TAB_PRO = 'TAB_PRO';
    const TAB_PROJECT = 'TAB_PROJECT';


    const QUERY_ALL = 'ALL';
    const QUERY_RECOVERY = 'RECOVERY';
    const QUERY_PROJECT = 'PROJECT';

    /** @var FormFactoryInterface */
    protected $formFactory;
    /** @var VehicleInfoAggregator */
    private $vehicleInfoAggregator;
    /** @var SearchResultProvider */
    private $searchResultProvider;
    /** @var PersonalVehicleEditionService */
    private $personalVehicleEditionService;
    /** @var ProVehicleEditionService */
    private $proVehicleEditionService;
    /** @var UserEditionService */
    private $userEditionService;

    /**
     * SearchController constructor.
     * @param FormFactoryInterface $formFactory
     * @param VehicleInfoAggregator $vehicleInfoAggregator
     * @param SearchResultProvider $searchResultProvider
     * @param PersonalVehicleEditionService $personalVehicleEditionService
     * @param ProVehicleEditionService $proVehicleEditionService
     * @param UserEditionService $userEditionService
     */
    public function __construct(
        FormFactoryInterface $formFactory,
        VehicleInfoAggregator $vehicleInfoAggregator,
        SearchResultProvider $searchResultProvider,
        PersonalVehicleEditionService $personalVehicleEditionService,
        ProVehicleEditionService $proVehicleEditionService,
        UserEditionService $userEditionService
    )
    {
        $this->formFactory = $formFactory;
        $this->vehicleInfoAggregator = $vehicleInfoAggregator;
        $this->searchResultProvider = $searchResultProvider;
        $this->personalVehicleEditionService = $personalVehicleEditionService;
        $this->proVehicleEditionService = $proVehicleEditionService;
        $this->userEditionService = $userEditionService;

    }

    public function searchAction(Request $request, string $type = self::TAB_ALL, int $page = 1): Response
    {
        $pages = [self::TAB_ALL => 1, self::TAB_PERSONAL => 1, self::TAB_PRO => 1, self::TAB_PROJECT => 1];
        $pages[$type] = $page;

        $searchForm = $this->getSearchForm($request, ($this->getUser() instanceof ProUser?'front_search_tab_personal':'front_search_tab_pro'), true);
        $searchForm->handleRequest($request);

        $searchResult = $this->searchResultProvider->getSearchResult($searchForm, $pages);

        $searchResultVehicles[self::TAB_ALL] = $this->userEditionService->getMixedBySearchResult($searchResult[self::TAB_ALL]);
        $searchResultVehicles[self::TAB_PERSONAL] = $this->personalVehicleEditionService->getVehiclesBySearchResult($searchResult[self::TAB_PERSONAL]);
        $searchResultVehicles[self::TAB_PRO] = $this->proVehicleEditionService->getVehiclesBySearchResult($searchResult[self::TAB_PRO]);
        $searchResultVehicles[self::TAB_PROJECT] = $this->userEditionService->getPersonalProjectsBySearchResult($searchResult[self::TAB_PROJECT]);

        $lastPage[self::TAB_ALL] = $searchResult[self::TAB_ALL]->numberOfPages();
        $lastPage[self::TAB_PERSONAL] = $searchResult[self::TAB_PERSONAL]->numberOfPages();
        $lastPage[self::TAB_PRO] = $searchResult[self::TAB_PRO]->numberOfPages();
        $lastPage[self::TAB_PROJECT] = $searchResult[self::TAB_PROJECT]->numberOfPages();

        return $this->render('front/Search/search.html.twig', [
            'searchForm' => $searchForm->createView(),
            'filterData' => (array)$searchForm->getData(),
            'result' => $searchResultVehicles,
            'pages' => $pages,
            'lastPage' => $lastPage,
            'tab' => $type
        ]);
    }

    /**
     * @param Request $request
     * @param int $page
     * @param string $type
     * @return Response
     */
    public function proAction(Request $request, int $page = 1, string $type): Response
    {
        $pages = [self::QUERY_ALL => 1, self::QUERY_PROJECT => 1, self::QUERY_RECOVERY => 1];
        $pages[$type] = $page;


        $searchForm = $this->getSearchForm($request, 'front_search_pro');
        $searchForm->handleRequest($request);

        $searchResult = $this->searchResultProvider->getSearchProResult($searchForm, $pages);
        $searchResultVehicles[self::QUERY_ALL] = $this->personalVehicleEditionService->getVehiclesBySearchResult($searchResult[self::QUERY_ALL]);
        $searchResultVehicles[self::QUERY_RECOVERY] = $this->personalVehicleEditionService->getVehiclesBySearchResult($searchResult[self::QUERY_RECOVERY]);
        $searchResultVehicles[self::QUERY_PROJECT] = $this->personalVehicleEditionService->getVehiclesBySearchResult($searchResult[self::QUERY_PROJECT]);

        $lastPage[self::QUERY_ALL] = $searchResult[self::QUERY_ALL]->numberOfPages();
        $lastPage[self::QUERY_RECOVERY] = $searchResult[self::QUERY_RECOVERY]->numberOfPages();
        $lastPage[self::QUERY_PROJECT] = $searchResult[self::QUERY_PROJECT]->numberOfPages();

        return $this->render('front/Search/pro_user_search.html.twig', [
            'searchForm' => $searchForm->createView(),
            'filterData' => $searchForm->getData(),
            'result' => $searchResultVehicles,
            'pages' => $pages,
            'lastPage' => $lastPage,
            'tab' => $type
        ]);
    }

    /**
     * @param Request $request
     * @param int $page
     * @return Response
     */
    public function personalAction(Request $request, int $page = 1): Response
    {
        $searchForm = $this->getSearchForm($request, 'front_search_personal');
        $searchForm->handleRequest($request);

        $searchResult = $this->searchResultProvider->getSearchPersonalResult($searchForm, $page);
        $searchResultVehicles = $this->proVehicleEditionService->getVehiclesBySearchResult($searchResult);

        $lastPage = $searchResult->numberOfPages();

        return $this->render('front/Search/personal_user_search.html.twig', [
            'searchForm' => $searchForm->createView(),
            'filterData' => $searchForm->getData(),
            'result' => $searchResultVehicles,
            'page' => $page,
            'lastPage' => $lastPage
        ]);
    }

    /**
     * @param Request $request
     * @param string $actionPath
     * @param null|bool $displaySortingField Display or not a field to sort result
     * @return \Symfony\Component\Form\FormInterface
     */
    private function getSearchForm(Request $request, string $actionPath, bool $displaySortingField = false)
    {
        $paramSearchVehicle = $request->query->get('search_vehicle');
        $filters = [
            'make' => $paramSearchVehicle['make'] ?? null,
            'model' => $paramSearchVehicle['model'] ?? null
        ];
        $availableValues = $this->vehicleInfoAggregator->getVehicleInfoAggregatesFromMakeAndModel($filters);
        $searchVehicleDTO = new SearchVehicleDTO();
        return $this->formFactory->create(SearchVehicleType::class, $searchVehicleDTO, [
            'action' => $this->generateRoute($actionPath),
            'available_values' => $availableValues,
            'sortingField' => $displaySortingField
        ]);
    }
}
