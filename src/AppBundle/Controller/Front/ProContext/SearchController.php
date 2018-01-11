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
    const QUERY_ALL = 'ALL';
    const QUERY_RECOVERY = 'RECOVERY';
    const QUERY_PROJECT = 'PROJECT';

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
    public function indexAction(Request $request, int $page = 1, $type): Response
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

        $queryBuilderBase[self::QUERY_ALL] = QueryBuilder::createNew(
            self::OFFSET + ($pages[self::QUERY_ALL] - 1) * $this->limit,
            $this->limit,
            self::MIN_SCORE
        );
        $queryBuilderBase[self::QUERY_RECOVERY] = QueryBuilder::createNew(
            self::OFFSET + ($pages[self::QUERY_RECOVERY] - 1) * $this->limit,
            $this->limit,
            self::MIN_SCORE
        );
        $queryBuilderBase[self::QUERY_PROJECT] = QueryBuilder::createNew(
            self::OFFSET + ($pages[self::QUERY_PROJECT] - 1) * $this->limit,
            $this->limit,
            self::MIN_SCORE
        );

        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            $queryBuilder[self::QUERY_ALL] = $this->completeQuery($queryBuilderBase[self::QUERY_ALL], $searchVehicleDTO, self::QUERY_ALL);
            $queryBuilder[self::QUERY_RECOVERY] = $this->completeQuery($queryBuilderBase[self::QUERY_RECOVERY], $searchVehicleDTO, self::QUERY_RECOVERY);
            $queryBuilder[self::QUERY_PROJECT] = $this->completeQuery($queryBuilderBase[self::QUERY_PROJECT], $searchVehicleDTO, self::QUERY_PROJECT);
        } else {
            $queryBuilder[self::QUERY_ALL] = $queryBuilderBase[self::QUERY_ALL];
            $queryBuilder[self::QUERY_RECOVERY] = $queryBuilderBase[self::QUERY_RECOVERY];
            $queryBuilder[self::QUERY_PROJECT] = $queryBuilderBase[self::QUERY_PROJECT];
        }

        $queryBody[self::QUERY_ALL] = $queryBuilder[self::QUERY_ALL]->getQueryBody();
        $queryBody[self::QUERY_RECOVERY] = $queryBuilder[self::QUERY_RECOVERY]->getQueryBody();
        $queryBody[self::QUERY_PROJECT] = $queryBuilder[self::QUERY_PROJECT]->getQueryBody();
        $searchResult[self::QUERY_ALL] = $this->queryExecutor->execute(
            $queryBody[self::QUERY_ALL],
            IndexablePersonalVehicle::TYPE
        );
        $searchResult[self::QUERY_RECOVERY] = $this->queryExecutor->execute(
            $queryBody[self::QUERY_RECOVERY],
            IndexablePersonalVehicle::TYPE
        );
        $searchResult[self::QUERY_PROJECT] = $this->queryExecutor->execute(
            $queryBody[self::QUERY_PROJECT],
            IndexablePersonalVehicle::TYPE
        );

        $lastPage[self::QUERY_ALL] = ceil($searchResult[self::QUERY_ALL]->totalHits() / $this->limit);
        $lastPage[self::QUERY_RECOVERY] = ceil($searchResult[self::QUERY_RECOVERY]->totalHits() / $this->limit);
        $lastPage[self::QUERY_PROJECT] = ceil($searchResult[self::QUERY_PROJECT]->totalHits() / $this->limit);

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
            //$queryBuilder->match('key_make', $searchVehicleDTO->text);
            if ($tab === self::QUERY_RECOVERY || $tab === self::QUERY_ALL) {
                $boolQuery->addClause(new MatchQuery('key_make', $searchVehicleDTO->text, CombiningFactor::SHOULD, ['operator' => 'OR']));
                $boolQuery->addClause(new MatchQuery('key_model', $searchVehicleDTO->text, CombiningFactor::SHOULD, ['operator' => 'OR']));
                $boolQuery->addClause(new MatchQuery('key_modelVersion', $searchVehicleDTO->text, CombiningFactor::SHOULD, ['operator' => 'OR']));
                $boolQuery->addClause(new MatchQuery('key_engine', $searchVehicleDTO->text, CombiningFactor::SHOULD, ['operator' => 'OR']));
            }
            if ($tab === self::QUERY_PROJECT || $tab === self::QUERY_ALL) {
                $boolQuery->addClause(new MatchQuery('projectVehicles.key_make', $searchVehicleDTO->text, CombiningFactor::SHOULD, ['operator' => 'OR']));
                $boolQuery->addClause(new MatchQuery('projectVehicles.key_model', $searchVehicleDTO->text, CombiningFactor::SHOULD, ['operator' => 'OR']));
            }
            $queryBuilder->addQuery($boolQuery);
        }
        if (!empty($searchVehicleDTO->cityName)) {
                $queryBuilder->addFilter(new GeoDistanceFilter('location', $searchVehicleDTO->latitude, $searchVehicleDTO->longitude, '300'));
        }
        if (!empty($searchVehicleDTO->make)) {
            if ($tab === self::QUERY_RECOVERY) {
                $queryBuilder->addFilter(new TermFilter('make', $searchVehicleDTO->make));
            }
            if ($tab === self::QUERY_PROJECT) {
                $queryBuilder->addFilter(new TermFilter('projectVehicles.make', $searchVehicleDTO->make));
            }
            if ($tab === self::QUERY_ALL) {
                $boolQueryMake = new BoolQuery();
                $boolQueryMake->addClause(new MatchQuery('make', $searchVehicleDTO->make, CombiningFactor::SHOULD, ['operator' => 'OR']));
                $boolQueryMake->addClause(new MatchQuery('projectVehicles.make', $searchVehicleDTO->make, CombiningFactor::SHOULD, ['operator' => 'OR']));
                $queryBuilder->addQuery($boolQueryMake);
            }
        }
        if (!empty($searchVehicleDTO->model)) {
            if ($tab === self::QUERY_RECOVERY) {
                $queryBuilder->addFilter(new TermFilter('model', $searchVehicleDTO->model));
            }
            if ($tab === self::QUERY_PROJECT) {
                $queryBuilder->addFilter(new TermFilter('projectVehicles.model', $searchVehicleDTO->model));
            }
            if ($tab === self::QUERY_ALL) {
                $boolQueryModel = new BoolQuery();
                $boolQueryModel->addClause(new MatchQuery('model', $searchVehicleDTO->model, CombiningFactor::SHOULD, ['operator' => 'OR']));
                $boolQueryModel->addClause(new MatchQuery('projectVehicles.model', $searchVehicleDTO->model, CombiningFactor::SHOULD, ['operator' => 'OR']));
                $queryBuilder->addQuery($boolQueryModel);
            }
        }
        if (!empty($searchVehicleDTO->mileageMax)) {
            if ($tab === self::QUERY_RECOVERY) {
                $queryBuilder->addFilter(new RangeFilter('mileage', $searchVehicleDTO->mileageMax, RangeFilter::LESS_THAN_OR_EQUAL_OPERATOR));
            }
            if ($tab === self::QUERY_PROJECT) {
                $queryBuilder->addFilter(new RangeFilter('projectVehicles.mileageMax', $searchVehicleDTO->mileageMax, RangeFilter::GREATER_THAN_OR_EQUAL_OPERATOR));
            }
            if ($tab === self::QUERY_ALL) {
                $boolQueryMileage = new BoolQuery();
                $boolQueryMileage->addClause(new RangeFilter('mileage', $searchVehicleDTO->mileageMax, RangeFilter::LESS_THAN_OR_EQUAL_OPERATOR));
                $boolQueryMileage->addClause(new RangeFilter('projectVehicles.mileageMax', $searchVehicleDTO->mileageMax, RangeFilter::GREATER_THAN_OR_EQUAL_OPERATOR));
                $queryBuilder->addQuery($boolQueryMileage);
            }
        }
        if (!empty($searchVehicleDTO->yearsMin)) {
            $queryBuilder->addFilter(new RangeFilter('years', $searchVehicleDTO->yearsMin, RangeFilter::GREATER_THAN_OR_EQUAL_OPERATOR));
        }
        if (!empty($searchVehicleDTO->yearsMax)) {
            $queryBuilder->addFilter(new RangeFilter('years', $searchVehicleDTO->yearsMax, RangeFilter::LESS_THAN_OR_EQUAL_OPERATOR));
        }
        if ($tab !== self::QUERY_PROJECT) {
            if (!empty($searchVehicleDTO->transmission)) {
                $queryBuilder->addFilter(new TermFilter('transmission', $searchVehicleDTO->transmission));
            }
            if (!empty($searchVehicleDTO->fuel)) {
                $queryBuilder->addFilter(new TermFilter('fuel', $searchVehicleDTO->fuel));
            }
        }

        return $queryBuilder;
    }
}
