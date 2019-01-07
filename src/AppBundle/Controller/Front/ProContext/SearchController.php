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

    public function searchAction(Request $request, int $page = 1): Response
    {
        $type = $request->query->get('search_vehicle', self::TAB_ALL);
        if(is_array($type)){
            $type = $type['tab'] ?? self::TAB_ALL;
        }

        $pages = [self::TAB_ALL => 1, self::TAB_PERSONAL => 1, self::TAB_PRO => 1, self::TAB_PROJECT => 1];
        $pages[$type] = $page;

        $searchForm = $this->getSearchForm($request, true);
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
     * @param string $actionPath
     * @param null|bool $displaySortingField Display or not a field to sort result
     * @return \Symfony\Component\Form\FormInterface
     */
    private function getSearchForm(Request $request, bool $displaySortingField = false)
    {
        $paramSearchVehicle = $request->query->get('search_vehicle');
        $filters = [
            'make' => $paramSearchVehicle['make'] ?? null,
            'model' => $paramSearchVehicle['model'] ?? null
        ];
        $availableValues = $this->vehicleInfoAggregator->getVehicleInfoAggregatesFromMakeAndModel($filters);
        $searchVehicleDTO = new SearchVehicleDTO();
        $actionRoute = $this->getUser() instanceof ProUser ?
            $this->generateRoute('front_search',[
                'search_vehicle' => ['tab' => self::TAB_PERSONAL]
            ]):
            $this->generateRoute('front_search',[
                'search_vehicle' => ['tab' => self::TAB_PRO]
            ]);
        return $this->formFactory->create(SearchVehicleType::class, $searchVehicleDTO, [
            'action' => $actionRoute,
            'available_values' => $availableValues,
            'sortingField' => $displaySortingField
        ]);
    }
}
