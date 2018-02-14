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
    public function getVehicleInfoAggregatesFromMakeAndModel(array $data = []): array
    {
        $aggregations = $this->getVehicleInfoAggregates([]);
        if(isset($data['make'])) {
            $aggregations = array_merge($aggregations, $this->getVehicleInfoAggregates(['make' => $data['make']]));
        }
        if(isset($data['model'])) {
            $aggregations = array_merge($aggregations, $this->getVehicleInfoAggregates(['model' => $data['model']]));
        }
        return $aggregations;
    }

    /**
     * @param array $data
     * @return array
     */
    public function getVehicleInfoAggregates(array $data = []): array
    {
        $qb = QueryBuilder::createNew(QueryBuilder::DEFAULT_OFFSET, 1000);

        foreach ($data as $field => $value) {
            if (!empty($value)) {
                $qb->addFilter(new TermFilter($field, $value));
            }
        }

        $aggregationMapping = [
            'make' => ['model'],
            'model' => ['engine', 'fuel'],
            'engine' => ['fuel'],
            'fuel' => ['engine'],
            'ktypNumber' => ['make', 'model', 'engine', 'fuel'],
        ];

        $qb->addAggregation(new Aggregation('fuel', 'terms', 'fuel'));
        if (empty($data)) {
            $qb->addAggregation(new Aggregation('make', 'terms', 'make', ['size' => 1000]));
        }

        $childAggregations = [];
        foreach ($aggregationMapping as $key => $children) {
            $childAggregations = (isset($data[$key]) && !empty($data[$key]) ? $children : $childAggregations);
        }
        foreach ($childAggregations as $aggregationField) {
            $qb->addAggregation(new Aggregation($aggregationField, 'terms', $aggregationField));
        }
        $result = $this->queryExecutor->execute($qb->getQueryBody(), VehicleInfo::TYPE);
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
