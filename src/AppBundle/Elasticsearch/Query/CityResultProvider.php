<?php


namespace AppBundle\Elasticsearch\Query;


use AppBundle\Elasticsearch\Type\IndexableCity;
use Novaway\ElasticsearchClient\Query\Result;
use Novaway\ElasticsearchClient\QueryExecutor;

class CityResultProvider
{

    /** @var QueryBuilderFilterer */
    private $queryBuilderFilterer;
    /** @var QueryExecutor */
    private $queryExecutor;

    public function __construct(
        QueryBuilderFilterer $queryBuilderFilterer,
        QueryExecutor $queryExecutor
    )
    {
        $this->queryBuilderFilterer = $queryBuilderFilterer;
        $this->queryExecutor = $queryExecutor;
    }

    public function provideForSearch(string $terms): Result
    {
        $qb = $this->queryBuilderFilterer->getQueryCity($terms);

        return $this->queryExecutor->execute(
            $qb->getQueryBody(),
            IndexableCity::TYPE
        );
    }

}
