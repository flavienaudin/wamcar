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
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Wamcar\User\ProUser;

class SearchController extends BaseController
{
    const TAB_ALL = 'tous';
    const TAB_PERSONAL = 'particulier';
    const TAB_PRO = 'professionnels';
    const TAB_PROJECT = 'souhaits';

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

        if ($request->query->has('tab')) {
            $type = $request->query->get('tab', self::TAB_ALL);
        } else {
            // Legacy
            $type = $request->get('search_vehicle', self::TAB_ALL);
            if (is_array($type)) {
                $type = $type['tab'] ?? self::TAB_ALL;
            }
            // Legacy conversion
            switch ($type) {
                case self::LEGACY_TAB_ALL:
                    $type = self::TAB_ALL;
                    break;
                case self::LEGACY_TAB_PERSONAL:
                    $type = self::TAB_PERSONAL;
                    break;
                case self::LEGACY_TAB_PRO:
                    $type = self::TAB_PRO;
                    break;
                case self::LEGACY_TAB_PROJECT:
                    $type = self::TAB_PROJECT;
                    break;
            }
        }
        // TODO use legacy $type to check search type object
        $searchForm = $this->getSearchForm($request, true);

        if ('GET' === $request->getMethod()) {
            // legacy GET form submission
            if ($request->query->has('search_vehicle')) {
                $searchForm->submit($request->query->get('search_vehicle'));
            }
        }

        $searchForm->handleRequest($request);
        $searchResult = $this->searchResultProvider->getSearchResult($searchForm->getData(), $page);
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
        if($make){
            $make = strtoupper($make);
        }

        $model = $request->get('model');
        if($model){
            $model = str_replace('-', ' ', $model);
        }

        $filters = [
            'make' => $make ?? $paramSearchVehicle['make'] ?? null,
            'model' => $model ?? $paramSearchVehicle['model'] ?? null
        ];

        $availableValues = $this->vehicleInfoIndexer->getVehicleInfoAggregatesFromMakeAndModel($filters);
        $searchVehicleDTO = new SearchVehicleDTO();
        $searchVehicleDTO->make = $filters['make'];
        $searchVehicleDTO->model = $filters['model'];

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
                        if ($hit['postalCode'] === $cityPostalCode) {
                            $searchVehicleDTO->postalCode = $hit['postalCode'];
                            $searchVehicleDTO->cityName = $hit['cityName'];
                            $searchVehicleDTO->latitude = $hit['latitude'];
                            $searchVehicleDTO->longitude = $hit['longitude'];
                            break;
                        }
                    }
                }
            }
        }

        $actionRoute = $this->getUser() instanceof ProUser ?
            $this->generateRoute('front_search', ['tab' => self::TAB_PERSONAL])
            : $this->generateRoute('front_search', ['tab' => self::TAB_PRO]);
        return $this->formFactory->create(SearchVehicleType::class, $searchVehicleDTO, [
            'action' => $actionRoute,
            'available_values' => $availableValues,
            'sortingField' => $displaySortingField
        ]);
    }
}
