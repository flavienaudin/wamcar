<?php


namespace AppBundle\Elasticsearch\Query;


use AppBundle\Controller\Front\ProContext\SearchController;
use AppBundle\Elasticsearch\Type\IndexablePersonalVehicle;
use Novaway\ElasticsearchClient\Query\QueryBuilder;
use Novaway\ElasticsearchClient\QueryExecutor;

class SearchResultProvider
{
    const LIMIT = 9;
    const MIN_SCORE = 0.05;
    const OFFSET = 0;

    /** @var QueryExecutor */
    private $queryExecutor;

    /** @var QueryBuilderFilterer */
    private $queryBuilderFilterer;

    /**
     * SearchResultProvider constructor.
     * @param QueryExecutor $queryExecutor
     * @param QueryBuilderFilterer $queryBuilderFilterer
     */
    public function __construct(QueryExecutor $queryExecutor, QueryBuilderFilterer $queryBuilderFilterer)
    {
        $this->queryExecutor = $queryExecutor;
        $this->queryBuilderFilterer = $queryBuilderFilterer;
    }

    public function getSearchResult($searchForm, $pages)
    {
        $searchVehicleDTO = $searchForm->getData();

        $queryBuilderBase = $this->initializeQueryBuilder($pages);

        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            $queryBuilder[SearchController::QUERY_ALL] = $this->queryBuilderFilterer->getQueryBuilder($queryBuilderBase[SearchController::QUERY_ALL], $searchVehicleDTO, SearchController::QUERY_ALL);
            $queryBuilder[SearchController::QUERY_RECOVERY] = $this->queryBuilderFilterer->getQueryBuilder($queryBuilderBase[SearchController::QUERY_RECOVERY], $searchVehicleDTO, SearchController::QUERY_RECOVERY);
            $queryBuilder[SearchController::QUERY_PROJECT] = $this->queryBuilderFilterer->getQueryBuilder($queryBuilderBase[SearchController::QUERY_PROJECT], $searchVehicleDTO, SearchController::QUERY_PROJECT);
        } else {
            $queryBuilder[SearchController::QUERY_ALL] = $queryBuilderBase[SearchController::QUERY_ALL];
            $queryBuilder[SearchController::QUERY_RECOVERY] = $queryBuilderBase[SearchController::QUERY_RECOVERY];
            $queryBuilder[SearchController::QUERY_PROJECT] = $queryBuilderBase[SearchController::QUERY_PROJECT];
        }

        $queryBody = $this->getQueryBody($queryBuilder);

        return $this->getQueryResult($queryBody);
    }

    /**
     * @param $pages
     * @return array|QueryBuilder[]
     */
    private function initializeQueryBuilder($pages)
    {
        $queryBuilderBase[SearchController::QUERY_ALL] = QueryBuilder::createNew(
            self::OFFSET + ($pages[SearchController::QUERY_ALL] - 1) * self::LIMIT,
            self::LIMIT,
            self::MIN_SCORE
        );
        $queryBuilderBase[SearchController::QUERY_RECOVERY] = QueryBuilder::createNew(
            self::OFFSET + ($pages[SearchController::QUERY_RECOVERY] - 1) * self::LIMIT,
            self::LIMIT,
            self::MIN_SCORE
        );
        $queryBuilderBase[SearchController::QUERY_PROJECT] = QueryBuilder::createNew(
            self::OFFSET + ($pages[SearchController::QUERY_PROJECT] - 1) * self::LIMIT,
            self::LIMIT,
            self::MIN_SCORE
        );

        return $queryBuilderBase;
    }

    /**
     * @param $queryBuilder
     * @return array
     */
    private function getQueryBody($queryBuilder)
    {
        $queryBody[SearchController::QUERY_ALL] = $queryBuilder[SearchController::QUERY_ALL]->getQueryBody();
        $queryBody[SearchController::QUERY_RECOVERY] = $queryBuilder[SearchController::QUERY_RECOVERY]->getQueryBody();
        $queryBody[SearchController::QUERY_PROJECT] = $queryBuilder[SearchController::QUERY_PROJECT]->getQueryBody();

        return $queryBody;
    }

    /**
     * @param $queryBody
     * @return array
     */
    private function getQueryResult($queryBody)
    {
        $searchResult[SearchController::QUERY_ALL] = $this->queryExecutor->execute(
            $queryBody[SearchController::QUERY_ALL],
            IndexablePersonalVehicle::TYPE
        );
        $searchResult[SearchController::QUERY_RECOVERY] = $this->queryExecutor->execute(
            $queryBody[SearchController::QUERY_RECOVERY],
            IndexablePersonalVehicle::TYPE
        );
        $searchResult[SearchController::QUERY_PROJECT] = $this->queryExecutor->execute(
            $queryBody[SearchController::QUERY_PROJECT],
            IndexablePersonalVehicle::TYPE
        );

        return $searchResult;
    }

}
