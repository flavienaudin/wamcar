<?php

namespace AppBundle\Controller\Front\ProContext;

use AppBundle\Controller\Front\BaseController;
use AppBundle\Elasticsearch\Query\SearchQuery;
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

    /** @var FormFactoryInterface */
    protected $formFactory;
    /** @var QueryExecutor */
    private $queryExecutor;
    /** @var VehicleInfoAggregator */
    private $vehicleInfoAggregator;
    /** @var int  */
    private $limit;

    /**
     * SearchController constructor.
     * @param FormFactoryInterface $formFactory
     * @param QueryExecutor $queryExecutor
     * @param VehicleInfoAggregator $vehicleInfoAggregator
     * @param int $limit
     */
    public function __construct(
        FormFactoryInterface $formFactory,
        QueryExecutor $queryExecutor,
        VehicleInfoAggregator $vehicleInfoAggregator,
        int $limit = 9
    )
    {
        $this->formFactory = $formFactory;
        $this->queryExecutor = $queryExecutor;
        $this->vehicleInfoAggregator = $vehicleInfoAggregator;
        $this->limit = $limit;

    }

    /**
     * @param Request $request
     * @param int $page
     * @return Response
     */
    public function indexAction(Request $request, int $page = 1): Response
    {
        $filters = [
            'make' => $request->query->get('search_vehicle')['make'],
            'model' => $request->query->get('search_vehicle')['model']
        ];
        $availableValues = $this->vehicleInfoAggregator->getVehicleInfoAggregatesFromMakeAndModel($filters);

        $searchVehicleDTO = new SearchVehicleDTO();
        $searchForm = $this->formFactory->create(SearchVehicleType::class, $searchVehicleDTO, [
            'method' => 'POST',
            'action' => $this->generateRoute('front_search_pro'),
            'available_values' => $availableValues
        ]);

        $searchForm->handleRequest($request);

        $queryBuilder = QueryBuilder::createNew(
            self::OFFSET + ($page - 1) * $this->limit,
            $this->limit,
            self::MIN_SCORE
        );

        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            $queryBuilder['ALL'] = $this->completeQuery($queryBuilder, $searchVehicleDTO, 'ALL');
            $queryBuilder['RECOVERY'] = $this->completeQuery($queryBuilder, $searchVehicleDTO, 'Recovery');
            $queryBuilder['PROJECT'] = $this->completeQuery($queryBuilder, $searchVehicleDTO, 'Project');
        }

        $queryBody['ALL'] = $queryBuilder['ALL']->getQueryBody();
        $queryBody['RECOVERY'] = $queryBuilder['RECOVERY']->getQueryBody();
        $queryBody['PROJECT'] = $queryBuilder['PROJECT']->getQueryBody();
        $searchResult['ALL'] = $this->queryExecutor->execute(
            $queryBody['ALL'],
            IndexablePersonalVehicle::TYPE
        );
        $searchResult['RECOVERY'] = $this->queryExecutor->execute(
            $queryBody['RECOVERY'],
            IndexablePersonalVehicle::TYPE
        );
        $searchResult['PROJECT'] = $this->queryExecutor->execute(
            $queryBody['PROJECT'],
            IndexablePersonalVehicle::TYPE
        );

        $lastPage['ALL'] = ceil($searchResult['ALL']->totalHits() / $this->limit);
        $lastPage['RECOVERY'] = ceil($searchResult['RECOVERY']->totalHits() / $this->limit);
        $lastPage['PROJECT'] = ceil($searchResult['PROJECT']->totalHits() / $this->limit);

        return $this->render('front/Search/search.html.twig', [
                'searchForm' => $searchForm->createView(),
                'filterData' => $searchVehicleDTO,
                'result' => $searchResult,
                'page' => $page,
                'lastPage' => $lastPage
            ])
        ;
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param SearchVehicleDTO $searchVehicleDTO
     * @param $tab
     * @return QueryBuilder
     */
    private function completeQuery(QueryBuilder $queryBuilder, SearchVehicleDTO $searchVehicleDTO, $tab)
    {
        if (!empty($searchVehicleDTO->text)) {
            $boolQuery = new BoolQuery();
            //Necessary for search only by key_make
            $queryBuilder->match('key_make', $searchVehicleDTO->text);
            if ($tab === 'RECOVERY' || $tab === 'ALL') {
                $boolQuery->addClause(new MatchQuery('key_make', $searchVehicleDTO->text, CombiningFactor::SHOULD, ['operator' => 'OR']));
                $boolQuery->addClause(new MatchQuery('key_model', $searchVehicleDTO->text, CombiningFactor::SHOULD, ['operator' => 'OR']));
                $boolQuery->addClause(new MatchQuery('key_modelVersion', $searchVehicleDTO->text, CombiningFactor::SHOULD, ['operator' => 'OR']));
                $boolQuery->addClause(new MatchQuery('key_engine', $searchVehicleDTO->text, CombiningFactor::SHOULD, ['operator' => 'OR']));
            } elseif ($tab === 'PROJECT' || $tab === 'ALL') {
                $boolQuery->addClause(new MatchQuery('projectVehicles.make', $searchVehicleDTO->text, CombiningFactor::SHOULD, ['operator' => 'OR']));
                $boolQuery->addClause(new MatchQuery('projectVehicles.model', $searchVehicleDTO->text, CombiningFactor::SHOULD, ['operator' => 'OR']));
            }
            $queryBuilder->addQuery($boolQuery);
        }
        if (!empty($searchVehicleDTO->cityName)) {
            $queryBuilder->addFilter(new GeoDistanceFilter('location', $searchVehicleDTO->latitude, $searchVehicleDTO->longitude, '300'));
        }
        if (!empty($searchVehicleDTO->make)) {
            $queryBuilder->addFilter(new TermFilter('make', $searchVehicleDTO->make));
        }
        if (!empty($searchVehicleDTO->model)) {
            $queryBuilder->addFilter(new TermFilter('model', $searchVehicleDTO->model));
        }
        if (!empty($searchVehicleDTO->mileageMax)) {
            $queryBuilder->addFilter(new RangeFilter('mileage', $searchVehicleDTO->mileageMax, RangeFilter::LESS_THAN_OR_EQUAL_OPERATOR));
        }
        if (!empty($searchVehicleDTO->yearsMin)) {
            $queryBuilder->addFilter(new RangeFilter('years', $searchVehicleDTO->yearsMin, RangeFilter::GREATER_THAN_OR_EQUAL_OPERATOR));
        }
        if (!empty($searchVehicleDTO->yearsMax)) {
            $queryBuilder->addFilter(new RangeFilter('years', $searchVehicleDTO->yearsMax, RangeFilter::LESS_THAN_OR_EQUAL_OPERATOR));
        }
        if (!empty($searchVehicleDTO->transmission)) {
            $queryBuilder->addFilter(new TermFilter('transmission', $searchVehicleDTO->transmission));
        }
        if (!empty($searchVehicleDTO->fuel)) {
            $queryBuilder->addFilter(new TermFilter('fuel', $searchVehicleDTO->fuel));
        }

        return $queryBuilder;
    }
}
