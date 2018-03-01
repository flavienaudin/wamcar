<?php


namespace AppBundle\Elasticsearch\Query;


use AppBundle\Elasticsearch\Builder\IndexableCityBuilder;
use AppBundle\Elasticsearch\Type\IndexableCity;
use Novaway\ElasticsearchClient\Query\Result;
use Novaway\ElasticsearchClient\QueryExecutor;

class CityResultProvider
{

    /** @var QueryBuilderFilterer */
    private $queryBuilderFilterer;
    /** @var QueryExecutor */
    private $queryExecutor;
    /** @var IndexableCityBuilder */
    private $resultTransformer;

    public function __construct(
        QueryBuilderFilterer $queryBuilderFilterer,
        QueryExecutor $queryExecutor,
        IndexableCityBuilder $resultTransformer
    )
    {
        $this->queryBuilderFilterer = $queryBuilderFilterer;
        $this->queryExecutor = $queryExecutor;
        $this->resultTransformer = $resultTransformer;
    }

    public function provideForSearch(string $terms): Result
    {
        $qb = $this->queryBuilderFilterer->getQueryCity($terms);

        return $this->queryExecutor->execute(
            $qb->getQueryBody(),
            IndexableCity::TYPE,
            $this->resultTransformer
        );
    }

}
