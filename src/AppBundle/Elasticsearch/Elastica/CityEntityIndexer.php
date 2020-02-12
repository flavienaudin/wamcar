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
        $boolQuery->setMinimumShouldMatch(1);

        foreach (explode(' ', $terms) as $term) {
            if(is_int($term[0]) ) { // to deal with Corse CP 2A.../2B...
                $postalCodeQuery = $qb->query()->prefix(['postalCode' => $term]);
                $boolQuery->addShould($postalCodeQuery);

                $postalCodeTermQuery = $qb->query()->term(['postalCode' => ['value' => $term]]);
                $boolQuery->addShould($postalCodeTermQuery);
            }else {
                $cityNamePrefixQuery = $qb->query()->prefix(['cityName' => ['value' => $term]]);
                $boolQuery->addShould($cityNamePrefixQuery);

                $cityNameTermQuery = $qb->query()->match();
                $cityNameTermQuery->setField('cityName' ,$term);
                $boolQuery->addShould($cityNameTermQuery);
            }
        }

        $mainQuery = new Query($boolQuery);
        $mainQuery->setSize(20);
        $mainQuery->setSort(['_score' => 'desc']);

        return $mainQuery;

    }

}