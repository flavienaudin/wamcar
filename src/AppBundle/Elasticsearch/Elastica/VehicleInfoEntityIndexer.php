<?php

namespace AppBundle\Elasticsearch\Elastica;


use Elastica\Query;
use Elastica\QueryBuilder;
use Elastica\ResultSet;

class VehicleInfoEntityIndexer extends EntityIndexer
{


    /**
     * @param string $ktypNumber
     * @return ResultSet
     */
    public function getVehicleInfoByKtypNumber(string $ktypNumber): ResultSet
    {
        $mainQuery = new Query();
        $qb = new QueryBuilder();

        $mainQuery->setQuery($qb->query()->term([
            'ktypNumber' => $ktypNumber
        ]));

        return $this->search($mainQuery);
    }

    /**
     * @param array $data
     * @return array
     */
    public function getVehicleInfoAggregatesFromMakeAndModel(array $data = []): array
    {
        $aggregations = $this->getVehicleInfoAggregates();

        if (isset($data['make'])) {
            $aggregations = array_merge($aggregations, $this->getVehicleInfoAggregates(['make' => $data['make']]));
            if (isset($data['model'])) {
                $aggregations = array_merge($aggregations, $this->getVehicleInfoAggregates([
                    'make' => $data['make'],
                    'model' => $data['model'],
                ]));
            }
            if (isset($data['engine'])) {
                $aggregations = array_merge($aggregations, $this->getVehicleInfoAggregates([
                    'make' => $data['make'],
                    'model' => $data['model'],
                    'engine' => $data['engine'],
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
        $mainQuery = new Query();

        $qb = new QueryBuilder();

        $boolQuery = $qb->query()->bool();
        foreach ($data as $field => $value) {
            if (!empty($value)) {
                $boolQuery->addMust($qb->query()->term([$field => $value]));
            }
        }
        $mainQuery->setQuery($boolQuery);

        $fuelTermsAgg = $qb->aggregation()->terms("fuel");
        $fuelTermsAgg->setField('fuel');
        $fuelTermsAgg->setSize(1000);
        $mainQuery->addAggregation($fuelTermsAgg);


        if (empty($data)) {
            $makeTermsAgg = $qb->aggregation()->terms("make");
            $makeTermsAgg->setField('make');
            $makeTermsAgg->setSize(1000); // Set size to 0 is not working to not limit the size
            $mainQuery->addAggregation($makeTermsAgg);
        }

        $childAggregations = [];
        $formattedAggregations = [];
        $aggregationMapping = [
            'make' => ['model', 'engine', 'fuel'],
            'model' => ['engine', 'fuel'],
            'engine' => ['fuel'],
            'fuel' => ['engine'],
            'ktypNumber' => ['make', 'model', 'engine', 'fuel'],
        ];
        foreach ($aggregationMapping as $key => $children) {
            $childAggregations = (isset($data[$key]) && !empty($data[$key]) ? $children : $childAggregations);
        }
        foreach ($childAggregations as $aggregationField) {
            $childTermsAgg = $qb->aggregation()->terms($aggregationField);
            $childTermsAgg->setField($aggregationField);
            $childTermsAgg->setSize(1000); // Set size to 0 is not working to not limit the size
            $mainQuery->addAggregation($childTermsAgg);
        }

        $resultSet = $this->search($mainQuery);
        foreach ($resultSet->getAggregations() as $field => $aggregation) {
            $cleanAggregation = array_map(function ($aggregationDetail) {
                return $aggregationDetail['key'];
            }, $aggregation['buckets']);
            sort($cleanAggregation);
            $formattedAggregations[$field] = array_combine($cleanAggregation, $cleanAggregation);
        }
        return $formattedAggregations;
    }
}