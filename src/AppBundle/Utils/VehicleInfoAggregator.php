<?php

namespace AppBundle\Utils;

use AppBundle\Elasticsearch\Type\VehicleInfo;
use Novaway\ElasticsearchClient\Aggregation\Aggregation;
use Novaway\ElasticsearchClient\Filter\TermFilter;
use Novaway\ElasticsearchClient\Query\QueryBuilder;
use Novaway\ElasticsearchClient\QueryExecutor;

class VehicleInfoAggregator
{
    /** @var QueryExecutor */
    private $queryExecutor;

    /**
     * RegistrationController constructor.
     *
     * @param QueryExecutor $queryExecutor
     */
    public function __construct(QueryExecutor $queryExecutor)
    {
        $this->queryExecutor = $queryExecutor;
    }

    /**
     * @param array $data
     * @return array
     */
    public function getVehicleInfoAggregates(array $data = []): array
    {
        $qb = QueryBuilder::createNew(QueryBuilder::DEFAULT_OFFSET, 10);

        foreach ($data as $field => $value) {
            if(!empty($value)) {
                $qb->addFilter(new TermFilter($field, $value));
            }
        }

        $qb->addAggregation(new Aggregation('make', 'terms', 'make'));
        $qb->addAggregation(new Aggregation('fuel', 'terms', 'fuel'));
        if (isset($data['make'])) {
            $qb->addAggregation(new Aggregation('model', 'terms', 'model'));
        }
        if (isset($data['model'])) {
            $qb->addAggregation(new Aggregation('modelVersion', 'terms', 'modelVersion'));
            $qb->addAggregation(new Aggregation('engine', 'terms', 'engine'));
        }
        $result = $this->queryExecutor->execute($qb->getQueryBody(), VehicleInfo::TYPE);

        $formattedAggregations = [];
        foreach ($result->aggregations() as $field => $aggregation) {
            $cleanAggregation = array_map(function ($aggregationDetail) {
                return $aggregationDetail['key'];
            }, $aggregation);
            sort($cleanAggregation);
            $formattedAggregations[$field] = array_combine($cleanAggregation, $cleanAggregation);
        }

        return $formattedAggregations;
    }
}
