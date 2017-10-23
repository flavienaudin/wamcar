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
        $qb = QueryBuilder::createNew(QueryBuilder::DEFAULT_OFFSET, 0);

        foreach ($data as $field => $value) {
            $qb->addFilter(new TermFilter($field, $value));
        }

        $qb->addAggregation(new Aggregation('makes', 'terms', 'make'));
        $qb->addAggregation(new Aggregation('fuels', 'terms', 'fuel'));
        if (isset($data['make'])) {
            $qb->addAggregation(new Aggregation('models', 'terms', 'model'));
        }
        if (isset($data['model'])) {
            $qb->addAggregation(new Aggregation('modelVersions', 'terms', 'engineName')); // TODO : add a version column
            $qb->addAggregation(new Aggregation('engines', 'terms', 'engineName'));
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
