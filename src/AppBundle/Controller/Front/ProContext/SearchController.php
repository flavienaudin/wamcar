<?php

namespace AppBundle\Controller\Front\ProContext;

use AppBundle\Controller\Front\BaseController;
use AppBundle\Elasticsearch\Elastica\CityEntityIndexer;
use AppBundle\Elasticsearch\Elastica\ElasticUtils;
use AppBundle\Elasticsearch\Elastica\SearchResultProvider;
use AppBundle\Elasticsearch\Elastica\VehicleInfoEntityIndexer;
use AppBundle\Form\DTO\SearchVehicleDTO;
use AppBundle\Form\Type\SearchVehicleType;
use AppBundle\Services\User\UserEditionService;
use AppBundle\Services\Vehicle\PersonalVehicleEditionService;
use AppBundle\Services\Vehicle\ProVehicleEditionService;
use AppBundle\Utils\SearchTypeChoice;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;
use Wamcar\User\PersonalUser;

class SearchController extends BaseController
{
    // Values of the Query Param "type"
    const QP_TYPE_PERSONAL_VEHICLES = 'reprises-particulier';
    const QP_TYPE_PERSONAL_PROJECT = 'souhaits-particulier';
    const QP_TYPE_PRO_VEHICLES = 'professionnels';

    const LEGACY_TAB_ALL = 'TAB_ALL';
    const LEGACY_TAB_PERSONAL = 'TAB_PERSONAL';
    const LEGACY_TAB_PRO = 'TAB_PRO';
    const LEGACY_TAB_PROJECT = 'TAB_PROJECT';

    /** @var FormFactoryInterface */
    protected $formFactory;
    /** @var VehicleInfoEntityIndexer */
    private $vehicleInfoIndexer;
    /** @var SearchResultProvider */
    private $searchResultProvider;
    /** @var PersonalVehicleEditionService */
    private $personalVehicleEditionService;
    /** @var ProVehicleEditionService */
    private $proVehicleEditionService;
    /** @var UserEditionService */
    private $userEditionService;
    /** @var CityEntityIndexer */
    private $cityEntityIndexer;


    /**
     * SearchController constructor.
     * @param FormFactoryInterface $formFactory
     * @param SearchResultProvider $searchResultProvider
     * @param PersonalVehicleEditionService $personalVehicleEditionService
     * @param ProVehicleEditionService $proVehicleEditionService
     * @param UserEditionService $userEditionService
     * @param CityEntityIndexer $cityEntityIndexer
     */
    public function __construct(
        FormFactoryInterface $formFactory,
        VehicleInfoEntityIndexer $vehicleInfoEntityIndexer,
        SearchResultProvider $searchResultProvider,
        PersonalVehicleEditionService $personalVehicleEditionService,
        ProVehicleEditionService $proVehicleEditionService,
        UserEditionService $userEditionService,
        CityEntityIndexer $cityEntityIndexer
    )
    {
        $this->formFactory = $formFactory;
        $this->vehicleInfoIndexer = $vehicleInfoEntityIndexer;
        $this->searchResultProvider = $searchResultProvider;
        $this->personalVehicleEditionService = $personalVehicleEditionService;
        $this->proVehicleEditionService = $proVehicleEditionService;
        $this->userEditionService = $userEditionService;
        $this->cityEntityIndexer = $cityEntityIndexer;

    }

    public function searchAction(Request $request, int $page = 1): Response
    {
        // Normal use is query param. Attribute $page if for legacy purpose
        $page = $request->query->get('page', $page);

        $searchForm = $this->getSearchForm($request, true);

        if (Request::METHOD_GET === $request->getMethod() && $request->query->has('search_vehicle')) {
            // legacy GET form submission
            $searchVehicleData = $request->query->get('search_vehicle');
            $searchForm->submit($searchVehicleData);
        }

        $searchForm->handleRequest($request);
        $searchResult = $this->searchResultProvider->getSearchResult($searchForm->getData(), $page, $this->getUser());
        $searchResultVehicles = $this->userEditionService->getMixedBySearchItemResult($searchResult);

        return $this->render('front/Search/search.html.twig', [
            'searchForm' => $searchForm->createView(),
            'filterData' => (array)$searchForm->getData(),
            'result' => $searchResultVehicles,
            'page' => $page,
            'lastPage' => ElasticUtils::numberOfPages($searchResult)
        ]);
    }

    /**
     * @param Request $request
     * @param null|bool $displaySortingField Display or not a field to sort result
     * @return \Symfony\Component\Form\FormInterface
     */
    private function getSearchForm(Request $request, bool $displaySortingField = false)
    {
        $paramSearchVehicle = $request->get('search_vehicle');

        $make = $request->get('make');
        if ($make) {
            $make = strtoupper($make);
        }

        $model = $request->get('model');
        if ($model) {
            $model = urldecode($model);
        }

        $filters = [
            'vehicle.make.keyword' => $make ?? $paramSearchVehicle['make'] ?? null,
            'vehicle.model.keyword' => $model ?? $paramSearchVehicle['model'] ?? null
        ];

        $availableValues = $this->searchResultProvider->getVehicleInfoFilterValue($filters);
        $searchVehicleDTO = new SearchVehicleDTO();
        $searchVehicleDTO->make = $filters['vehicle.make.keyword'];
        $searchVehicleDTO->model = $filters['vehicle.model.keyword'];

        // Result types of the search
        $type = null;
        if ($request->query->has('type')) {
            // Current version
            $qpType = $request->query->get('type');
            $types = explode(',', $qpType);
            $type = [];
            if(in_array(self::QP_TYPE_PERSONAL_VEHICLES, $types)){
                $type[] = SearchTypeChoice::SEARCH_PERSONAL_VEHICLE;
            }
            if(in_array(self::QP_TYPE_PRO_VEHICLES, $types)){
                $type[] = SearchTypeChoice::SEARCH_PRO_VEHICLE;
            }
            if(in_array(self::QP_TYPE_PERSONAL_PROJECT, $types)){
                $type[] = SearchTypeChoice::SEARCH_PERSONAL_PROJECT;
            }
            if(empty($type)){
                $type = null;
            }
        } elseif ($request->query->has('search_vehicle')) {
            $searchVehicleQueryParam = $request->query->get('search_vehicle');
            if (is_array($searchVehicleQueryParam) && isset($searchVehicleQueryParam['tab'])) {
                // legacy GET form submission
                $type = $searchVehicleQueryParam['tab'];
                // do not exist anymore in SearchVehicleType
                unset($searchVehicleQueryParam['tab']);

                // Legacy conversion
                switch ($type) {
                    case self::LEGACY_TAB_PERSONAL:
                        $type = SearchTypeChoice::SEARCH_PERSONAL_VEHICLE;
                        break;
                    case self::LEGACY_TAB_PRO:
                        $type = SearchTypeChoice::SEARCH_PRO_VEHICLE;
                        break;
                    case self::LEGACY_TAB_PROJECT:
                        $type = SearchTypeChoice::SEARCH_PERSONAL_PROJECT;
                        break;
                    default:
                        $type = null;
                }
                if ($type != null) {
                    $searchVehicleQueryParam['type'] = $type;
                }
                $request->query->replace(['search_vehicle' => $searchVehicleQueryParam]);
            }
        }
        if (Request::METHOD_POST !== $request->getMethod()) {
            // Form not submitted as POST method : we compute the default value of 'type' field
            // If methode POST then the "type" field is defined by submitted data
            if ($type != null) {
                // URL param for the type of search object
                if (!is_array($type)) {
                    $type = [$type];
                }
                $searchVehicleDTO->type = $type;
            } else if ($this->isGranted(AuthenticatedVoter::IS_AUTHENTICATED_REMEMBERED, $this->getUser())) {
                if ($this->getUser() instanceof PersonalUser) {
                    $searchVehicleDTO->type = [SearchTypeChoice::SEARCH_PRO_VEHICLE];
                } else {
                    $searchVehicleDTO->type = [SearchTypeChoice::SEARCH_PERSONAL_PROJECT, SearchTypeChoice::SEARCH_PERSONAL_VEHICLE];
                }
            } else {
                $searchVehicleDTO->type = [SearchTypeChoice::SEARCH_PRO_VEHICLE];
            }
        }
        if(empty($searchVehicleDTO->type)){
            $searchVehicleDTO->type = [SearchTypeChoice::SEARCH_PRO_VEHICLE];
        }


        // Champ libre
        if ($request->query->has('q')) {
            $searchVehicleDTO->text = $request->query->get('q');
        }

        // Deal with ByCity action
        if (($city = $request->get('city')) !== null) {
            $idxSplit = strpos($city, '-');
            if ($idxSplit !== false) {
                $cityPostalCode = substr($city, 0, $idxSplit);
                $cityName = substr($city, $idxSplit + 1);
                $citiesResultSet = $this->cityEntityIndexer->provideForSearch($cityName);
                if ($citiesResultSet->getTotalHits() > 0) {
                    foreach ($citiesResultSet->getResults() as $result) {
                        $hit = $result->getData();
                        $postalCodes = explode('/', $hit['postalCode']);
                        if (in_array($cityPostalCode, $postalCodes)) {
                            $searchVehicleDTO->postalCode = $hit['postalCode'];
                            $searchVehicleDTO->cityName = $hit['cityName'];
                            $searchVehicleDTO->latitude = $hit['latitude'];
                            $searchVehicleDTO->longitude = $hit['longitude'];
                            $searchVehicleDTO->radius = 50;
                            break;
                        }
                    }
                }
            }
        }
        return $this->formFactory->create(SearchVehicleType::class, $searchVehicleDTO, [
            'action' => $this->generateRoute('front_search'),
            'available_values' => $availableValues,
            'sortingField' => $displaySortingField
        ]);
    }


    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function updateSeacrhVehicleFormAction(Request $request): JsonResponse
    {
        $filters = $request->get('filters', []);
        $fetch = $request->get('fetch', null);

        $aggregates = $this->searchResultProvider->getVehicleInfoFilterValue($filters);

        return new JsonResponse($fetch ? $aggregates[$fetch] : $aggregates);
    }
}
