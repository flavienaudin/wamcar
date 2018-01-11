<?php

namespace AppBundle\Controller\Front\ProContext;

use AppBundle\Controller\Front\BaseController;
use AppBundle\Elasticsearch\Query\SearchQuery;
use AppBundle\Elasticsearch\Query\SearchResultProvider;
use AppBundle\Elasticsearch\Type\IndexablePersonalVehicle;
use AppBundle\Form\DTO\SearchVehicleDTO;
use AppBundle\Form\Type\SearchVehicleType;
use AppBundle\Utils\VehicleInfoAggregator;
use Novaway\ElasticsearchClient\Filter\GeoDistanceFilter;
use Novaway\ElasticsearchClient\Filter\RangeFilter;
use Novaway\ElasticsearchClient\Filter\TermFilter;
use Novaway\ElasticsearchClient\Query\BoolQuery;
use Novaway\ElasticsearchClient\Query\CombiningFactor;
use Novaway\ElasticsearchClient\Query\MatchQuery;
use Novaway\ElasticsearchClient\Query\QueryBuilder;
use Novaway\ElasticsearchClient\QueryExecutor;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SearchController extends BaseController
{
    const MIN_SCORE = 0.05;
    const OFFSET = 0;
    const QUERY_ALL = 'ALL';
    const QUERY_RECOVERY = 'RECOVERY';
    const QUERY_PROJECT = 'PROJECT';

    /** @var FormFactoryInterface */
    protected $formFactory;
    /** @var VehicleInfoAggregator */
    private $vehicleInfoAggregator;
    /** @var SearchResultProvider */
    private $searchResultProvider;

    /**
     * SearchController constructor.
     * @param FormFactoryInterface $formFactory
     * @param VehicleInfoAggregator $vehicleInfoAggregator
     */
    public function __construct(
        FormFactoryInterface $formFactory,
        QueryExecutor $queryExecutor,
        VehicleInfoAggregator $vehicleInfoAggregator,
        SearchResultProvider $searchResultProvider
    )
    {
        $this->formFactory = $formFactory;
        $this->vehicleInfoAggregator = $vehicleInfoAggregator;
        $this->searchResultProvider = $searchResultProvider;

    }

    /**
     * @param Request $request
     * @param int $page
     * @param string $type
     * @return Response
     */
    public function indexAction(Request $request, int $page = 1, string $type): Response
    {
        $pages = [self::QUERY_ALL => 1, self::QUERY_PROJECT => 1, self::QUERY_RECOVERY => 1];
        $pages[$type] = $page;

        $filters = [
            'make' => $request->query->get('search_vehicle')['make'],
            'model' => $request->query->get('search_vehicle')['model']
        ];
        $availableValues = $this->vehicleInfoAggregator->getVehicleInfoAggregatesFromMakeAndModel($filters);

        $searchVehicleDTO = new SearchVehicleDTO();
        $searchForm = $this->formFactory->create(SearchVehicleType::class, $searchVehicleDTO, [
            'method' => 'GET',
            'action' => $this->generateRoute('front_search_pro'),
            'available_values' => $availableValues
        ]);

        $searchForm->handleRequest($request);

        $searchResult = $this->searchResultProvider->getSearchResult($searchForm, $pages);

        $lastPage[self::QUERY_ALL] = $searchResult[self::QUERY_ALL]->numberOfPages();
        $lastPage[self::QUERY_RECOVERY] = $searchResult[self::QUERY_RECOVERY]->numberOfPages();
        $lastPage[self::QUERY_PROJECT] = $searchResult[self::QUERY_PROJECT]->numberOfPages();

        return $this->render('front/Search/search.html.twig', [
                'searchForm' => $searchForm->createView(),
                'filterData' => $searchVehicleDTO,
                'result' => $searchResult,
                'pages' => $pages,
                'lastPage' => $lastPage,
                'tab' => $type
            ])
        ;
    }
}
