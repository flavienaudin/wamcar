<?php

namespace AppBundle\Controller\Front\ProContext;


use AppBundle\Controller\Front\BaseController;
use AppBundle\Elasticsearch\Elastica\CityEntityIndexer;
use AppBundle\Elasticsearch\Elastica\ElasticUtils;
use AppBundle\Elasticsearch\Elastica\ProUserEntityIndexer;
use AppBundle\Elasticsearch\Elastica\SearchResultProvider;
use AppBundle\Form\DTO\SearchProDTO;
use AppBundle\Form\DTO\SearchVehicleDTO;
use AppBundle\Form\Type\SearchProType;
use AppBundle\Services\User\ProServiceService;
use AppBundle\Services\User\UserEditionService;
use AppBundle\Utils\SearchTypeChoice;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Wamcar\User\ProService;
use Wamcar\User\ProServiceCategory;

class DirectoryController extends BaseController
{

    /** @var FormFactoryInterface */
    protected $formFactory;
    /** @var ProUserEntityIndexer */
    private $proUserEntityIndexer;
    /** @var UserEditionService */
    private $userEditionService;
    /** @var ProServiceService */
    private $proServiceService;
    /** @var CityEntityIndexer */
    private $cityEntityIndexer;
    /** @var SearchResultProvider */
    private $searchResultProvider;

    /**
     * DirectoryController constructor.
     * @param FormFactoryInterface $formFactory
     * @param ProUserEntityIndexer $proUserEntityIndexer
     * @param UserEditionService $userEditionService
     * @param ProServiceService $proServiceService
     * @param CityEntityIndexer $cityEntityIndexer
     * @param SearchResultProvider $searchResultProvider
     */
    public function __construct(
        FormFactoryInterface $formFactory,
        ProUserEntityIndexer $proUserEntityIndexer,
        UserEditionService $userEditionService,
        ProServiceService $proServiceService,
        CityEntityIndexer $cityEntityIndexer,
        SearchResultProvider $searchResultProvider
    )
    {
        $this->formFactory = $formFactory;
        $this->proUserEntityIndexer = $proUserEntityIndexer;
        $this->userEditionService = $userEditionService;
        $this->proServiceService = $proServiceService;
        $this->cityEntityIndexer = $cityEntityIndexer;
        $this->searchResultProvider = $searchResultProvider;
    }

    public function viewAction(Request $request, int $page = 1)
    {
        // Normal use is query param. Attribute $page if for legacy purpose
        $page = $request->query->get('page', $page);

        // Search advisors
        $searchProDTO = new SearchProDTO();
        $searchProDTO->searchTextInService = true;

        // Search vehicles
        $searchVehicleDTO = new SearchVehicleDTO();
        $searchVehicleDTO->type = [SearchTypeChoice::SEARCH_PRO_VEHICLE];
        $seeAllVehicleSearchRouteName = 'front_search';
        $seeAllVehicleSearchRouteParam = ['type' => SearchController::QP_TYPE_PRO_VEHICLES];

        // Champ libre
        $searchProDTO->text = $request->query->get('q');
        $searchVehicleDTO->text = $request->query->get('q');

        // Services
        $querySelectedService = null;
        if (($serviceName = $request->get('speciality')) !== null) {
            $querySelectedService = $this->proServiceService->getProServiceBySlug($serviceName);
        }

        // Deal with ByCity action
        if (($city = $request->get('city')) !== null) {
            $idxSplit = strpos($city, '-');
            if ($idxSplit !== false) {
                $cityPostalCode = substr($city, 0, $idxSplit);
                $cityName = urldecode(substr($city, $idxSplit + 1));
                $citiesResultSet = $this->cityEntityIndexer->provideForSearch($cityName);
                if ($citiesResultSet->getTotalHits() > 0) {
                    foreach ($citiesResultSet->getResults() as $result) {
                        $hit = $result->getData();
                        $postalCodes = explode('/', $hit['postalCode']);
                        if (in_array($cityPostalCode, $postalCodes)) {
                            $searchProDTO->postalCode = $hit['postalCode'];
                            $searchProDTO->cityName = $hit['cityName'];
                            $searchProDTO->latitude = $hit['latitude'];
                            $searchProDTO->longitude = $hit['longitude'];

                            break;
                        }
                    }
                }
            }
        }

        $proServicesInUse = $this->proServiceService->getProServiceByNames($this->proUserEntityIndexer->getProServices());
        $mainFilters = [];
        /** @var ProService $proService */
        foreach ($proServicesInUse as $proService) {
            if ($proService->getCategory()->getPositionMainFilter() != null) {
                if (!isset($mainFilters[$proService->getCategory()->getPositionMainFilter()])) {
                    $mainFilters[$proService->getCategory()->getPositionMainFilter()] = [
                        'category' => $proService->getCategory(),
                        'services' => []
                    ];
                }
                $mainFilters[$proService->getCategory()->getPositionMainFilter()]['services'][] = $proService;
            }
        }
        ksort($mainFilters);
        $searchProForm = $this->formFactory->create(SearchProType::class, $searchProDTO, [
            'action' => $this->generateRoute('front_directory_view'),
            'mainFilters' => $mainFilters,
            'selectedService' => $querySelectedService
        ]);

        $searchProForm->handleRequest($request);
        foreach ($mainFilters as $positionMainFilter => $filterParam) {
            /** @var ProServiceCategory $filterCategory */
            $filterCategory = $filterParam['category'];

            $categoryFieldName = SearchProType::getCategoryFieldName($filterCategory);
            $filterForm = $searchProForm->get($categoryFieldName);
            if ($filterForm != null && !empty($filterData = $filterForm->getData())) {
                $searchProDTO->filters[$categoryFieldName] = $filterData;
            }
        }


        // Construction de la recherche de voitures : Filtre selon la localisation
        if (!empty($searchProDTO->postalCode) && !empty($searchProDTO->cityName) && !empty($searchProDTO->latitude && !empty($searchProDTO->longitude))) {
            $searchVehicleDTO->postalCode = $searchProDTO->postalCode;
            $searchVehicleDTO->cityName = $searchProDTO->cityName;
            $searchVehicleDTO->latitude = $searchProDTO->latitude;
            $searchVehicleDTO->longitude = $searchProDTO->longitude;

            $seeAllVehicleSearchRouteName = 'front_search_by_city';

            // Ajout de la ville dans le querystring de l'url "tout voir"
            $seeAllVehicleSearchRouteParam['city'] = explode('/', $searchProDTO->postalCode)[0] . '-' . urlencode($searchProDTO->cityName);
        }

        // Recherche par champ libre
        $searchVehicleDTO->text = $searchProDTO->text;
        if(!empty($searchProDTO->text)) {
            // Ajout du champ libre dans le querystring de l'url "tout voir"
            $seeAllVehicleSearchRouteParam['q'] = $searchProDTO->text;
        }

        $proUsersResultSet = $this->proUserEntityIndexer->getQueryDirectoryProUserResult($searchProDTO, $page, $this->getUser());
        $proUserResult = $this->userEditionService->getUsersBySearchResult($proUsersResultSet);

        $searchVehiclesResultSet = $this->searchResultProvider->getSearchResult($searchVehicleDTO, $page, $this->getUser(), 4);
        $searchVehiclesItems = $this->userEditionService->getMixedBySearchItemResult($searchVehiclesResultSet);

        return $this->render('front/Directory/view.html.twig', [
            'header_search' => !empty($searchProDTO->text) ? $searchProDTO->text : ($querySelectedService != null ? $querySelectedService->getName() : null),
            'searchProForm' => $searchProForm->createView(),
            'filterData' => (array)$searchProForm->getData(),
            'proUsers' => [
                'result' => $proUserResult,
                'page' => $page,
                'lastPage' => ElasticUtils::numberOfPages($proUsersResultSet)
            ],
            'vehicles' => [
                'result' => $searchVehiclesItems,
                'page' => $page,
                'lastPage' => ElasticUtils::numberOfPages($searchVehiclesResultSet),
                'see_all_vehicle_search_url' => $this->router->generate($seeAllVehicleSearchRouteName, $seeAllVehicleSearchRouteParam)
            ]
        ]);
    }
}