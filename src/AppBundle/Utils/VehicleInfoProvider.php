<?php

namespace AppBundle\Utils;


use AppBundle\Elasticsearch\Query\QueryBuilderFilterer;
use AppBundle\Elasticsearch\Type\IndexableVehicleInfo;
use Novaway\ElasticsearchClient\QueryExecutor;

class VehicleInfoProvider
{
    /** @var QueryExecutor */
    private $queryExecutor;
    /** @var QueryBuilderFilterer */
    private $queryBuilderFilterer;

    /**
     * VehicleInfoProvider constructor.
     *
     * @param QueryExecutor $queryExecutor
     * @param QueryBuilderFilterer $queryBuilderFilterer
     */
    public function __construct(
        QueryExecutor $queryExecutor,
        QueryBuilderFilterer $queryBuilderFilterer
    )
    {
        $this->queryExecutor = $queryExecutor;
        $this->queryBuilderFilterer = $queryBuilderFilterer;
    }

    /**
     * @param string $ktypnr
     * @return array
     */
    public function getVehicleInfoByKtypNumber(string $ktypnr): array
    {
        $qb = $this->queryBuilderFilterer->getQueryVehicleInfoByKtypNumber($ktypnr);
        $result = $this->queryExecutor->execute($qb->getQueryBody(), IndexableVehicleInfo::TYPE);
        return $result->hits();
    }
}