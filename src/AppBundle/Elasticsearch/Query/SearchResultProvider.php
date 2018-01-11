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

    /** @var array */
    private $queryTypes;

    /**
     * SearchResultProvider constructor.
     * @param QueryExecutor $queryExecutor
     * @param QueryBuilderFilterer $queryBuilderFilterer
     */
    public function __construct(QueryExecutor $queryExecutor, QueryBuilderFilterer $queryBuilderFilterer)
    {
        $this->queryExecutor = $queryExecutor;
        $this->queryBuilderFilterer = $queryBuilderFilterer;
        $this->queryTypes = [SearchController::QUERY_ALL, SearchController::QUERY_RECOVERY, SearchController::QUERY_PROJECT];
    }

    public function getSearchResult($searchForm, $pages)
    {
        $searchVehicleDTO = $searchForm->getData();

        $queryBuilderBase = $this->initializeQueryBuilder($pages);
        $queryBuilder = [];

        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            foreach ($this->queryTypes as $queryType) {
                $queryBuilder[$queryType] = $this->queryBuilderFilterer->getQueryBuilder($queryBuilderBase[$queryType], $searchVehicleDTO, $queryType);
            }
        } else {
            foreach ($this->queryTypes as $queryType) {
                $queryBuilder[$queryType] = $queryBuilderBase[$queryType];
            }
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
        $queryBuilderBase = [];

        foreach ($this->queryTypes as $queryType) {
            $queryBuilderBase[$queryType] = QueryBuilder::createNew(
                self::OFFSET + ($pages[$queryType] - 1) * self::LIMIT,
                self::LIMIT,
                self::MIN_SCORE
            );
        }

        return $queryBuilderBase;
    }

    /**
     * @param $queryBuilder
     * @return array
     */
    private function getQueryBody($queryBuilder)
    {
        $queryBody = [];

        foreach ($this->queryTypes as $queryType) {
            $queryBody[$queryType] = $queryBuilder[$queryType]->getQueryBody();
        }

        return $queryBody;
    }

    /**
     * @param $queryBody
     * @return array
     */
    private function getQueryResult($queryBody)
    {
        $searchResult = [];

        foreach ($this->queryTypes as $queryType) {
            $searchResult[$queryType] = $this->queryExecutor->execute(
                $queryBody[$queryType],
                IndexablePersonalVehicle::TYPE
            );
        }

        return $searchResult;
    }

}
