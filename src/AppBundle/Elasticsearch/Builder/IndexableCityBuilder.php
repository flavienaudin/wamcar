<?php

namespace AppBundle\Elasticsearch\Builder;

use AppBundle\Doctrine\Entity\ApplicationCity;
use AppBundle\Elasticsearch\Formatter\CityOptionFormatter;
use AppBundle\Elasticsearch\Type\IndexableCity;
use Novaway\ElasticsearchClient\Query\Result;
use Novaway\ElasticsearchClient\Query\ResultTransformer;

class IndexableCityBuilder implements ResultTransformer
{
    /** @var CityOptionFormatter */
    private $cityOptionFormatter;

    public function __construct(
        CityOptionFormatter $cityOptionFormatter
    )
    {
        $this->cityOptionFormatter = $cityOptionFormatter;
    }



    /**
     * @param ApplicationCity $city
     * @return IndexableCity
     */
    public function buildFromApplicationCity(ApplicationCity $city): IndexableCity
    {
        return new IndexableCity(
            $city->getInsee(),
            $city->getPostalCode(),
            $city->getCityName(),
            $city->getLatitude(),
            $city->getLongitude()
        );
    }

    public function formatResult(Result $result): Result
    {
        return new Result(
            $result->totalHits(),
            \array_map(function (array $hit) {
                $value = $this->cityOptionFormatter->formatCityOption($hit['postalCode'], $hit['cityName']);
                return [
                    'id' => $hit['postalCode'],
                    'cityName' => $hit['cityName'],
                    'latitude' => $hit['latitude'],
                    'longitude' => $hit['longitude'],
                    'text' => $value,
                ];
            }, $result->hits()),
            $result->aggregations()
        );
    }
}
