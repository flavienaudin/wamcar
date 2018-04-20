<?php

namespace AppBundle\Utils;

use AppBundle\Elasticsearch\Query\QueryBuilderFilterer;
use AppBundle\Elasticsearch\Type\VehicleInfo;
use Novaway\ElasticsearchClient\Aggregation\Aggregation;
use Novaway\ElasticsearchClient\Filter\TermFilter;
use Novaway\ElasticsearchClient\Query\QueryBuilder;
use Novaway\ElasticsearchClient\QueryExecutor;

class VehicleInfoAggregator
{
    /** @var QueryExecutor */
    private $queryExecutor;
    /** @var QueryBuilderFilterer */
    private $queryBuilderFilterer;

    /**
     * RegistrationController constructor.
     *
     * @param QueryExecutor $queryExecutor
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
     * @param array $data
     * @return array
     */
    public function getVehicleInfoAggregatesFromMakeAndModel(array $data = []): array
    {
        $aggregations = $this->getVehicleInfoAggregates();

        if(isset($data['make'])) {
            $aggregations = array_merge($aggregations, $this->getVehicleInfoAggregates(['make' => $data['make']]));
            if(isset($data['model'])) {
                $aggregations = array_merge($aggregations, $this->getVehicleInfoAggregates([
                    'make' => $data['make'],
                    'model' => $data['model'],
                ]));
            }

        }
        return $aggregations;
    }

    /**
     * @param array $data
     * @return array
     */
    public function getVehicleInfoAggregates(array $data = []): array
    {
        $qb = $this->queryBuilderFilterer->getQueryVehicleInfo($data);

        $aggregationMapping = [
            'make' => ['model', 'engine', 'fuel'],
            'model' => ['engine', 'fuel'],
            'engine' => ['fuel'],
            'fuel' => ['engine'],
            'ktypNumber' => ['make', 'model', 'engine', 'fuel'],
        ];

        // 'size' => 0 => mean unlimited
        $qb->addAggregation(new Aggregation('fuel', 'terms', 'fuel', ['size' => 0]));
        if (empty($data)) {
            $qb->addAggregation(new Aggregation('make', 'terms', 'make', ['size' => 0]));
        }

        $childAggregations = [];
        $formattedAggregations = [];
        foreach ($aggregationMapping as $key => $children) {
            $childAggregations = (isset($data[$key]) && !empty($data[$key]) ? $children : $childAggregations);
        }
        foreach ($childAggregations as $aggregationField) {
            $qb->addAggregation(new Aggregation($aggregationField, 'terms', $aggregationField, ['size' => 0]));
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
