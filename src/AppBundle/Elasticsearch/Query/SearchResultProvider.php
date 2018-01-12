<?php


namespace AppBundle\Elasticsearch\Query;


use AppBundle\Controller\Front\ProContext\SearchController;
use AppBundle\Elasticsearch\Type\IndexablePersonalVehicle;
use AppBundle\Form\DTO\SearchVehicleDTO;
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
        $formValidate = $searchForm->isSubmitted() && $searchForm->isValid();

        $searchResult = [];
        foreach ($this->queryTypes as $queryType) {
            $searchResult[$queryType] = $this->getQueryResult($queryType, $searchVehicleDTO, $pages, $formValidate);
        }

        return $searchResult;
    }

    /**
     * @param string $queryType
     * @param SearchVehicleDTO $searchVehicleDTO
     * @param array $pages
     * @param bool $submittedAndValid
     * @return \Novaway\ElasticsearchClient\Query\Result
     */
    private function getQueryResult(string $queryType, SearchVehicleDTO $searchVehicleDTO, array $pages, bool $submittedAndValid)
    {

        $queryBuilder = QueryBuilder::createNew(
            self::OFFSET + ($pages[$queryType] - 1) * self::LIMIT,
            self::LIMIT,
            self::MIN_SCORE
        );

        if ($submittedAndValid) {
            $queryBuilder = $this->queryBuilderFilterer->getQueryBuilder($queryBuilder, $searchVehicleDTO, $queryType);
        }

        return $this->queryExecutor->execute(
            $queryBuilder->getQueryBody(),
            IndexablePersonalVehicle::TYPE
        );
    }
}
