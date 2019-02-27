<?php

namespace AppBundle\Elasticsearch\Elastica;


use Elastica\Query;
use Elastica\QueryBuilder;
use Elastica\ResultSet;

class CityEntityIndexer extends EntityIndexer
{

    /**
     * @param string $terms
     * @return ResultSet
     */
    public function provideForSearch(string $terms): ResultSet
    {
        return $this->search($this->getQueryCity($terms));
    }

    /**
     * @param string $terms
     * @return Query
     */
    private function getQueryCity(string $terms): Query
    {
        $terms = strtolower($terms);

        $qb = new QueryBuilder();
        $boolQuery = $qb->query()->bool();

        foreach (explode(' ', $terms) as $term) {
            $postalCodeQuery = $qb->query()->prefix(['postalCode' => $term]);
            $boolQuery->addShould($postalCodeQuery);

            $postalCodeTermQuery = $qb->query()->term(['postalCode' => ['value' => $term, 'boost' => 2]]);
            $boolQuery->addShould($postalCodeTermQuery);

            $cityNameMatchQuery = $qb->query()->match('cityName' ,$term);
            $boolQuery->addShould($cityNameMatchQuery);

            $cityNamePrefixQuery = $qb->query()->prefix(['cityName' => $term]);
            $boolQuery->addShould($cityNamePrefixQuery );
        }

        $mainQuery = new Query($boolQuery);
        $mainQuery->setSize(20);
        $mainQuery->setSort(['_score' => 'desc']);

        return $mainQuery;

    }

}