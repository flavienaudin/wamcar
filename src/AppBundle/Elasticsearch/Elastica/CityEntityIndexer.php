<?php

namespace AppBundle\Elasticsearch\Elastica;


use Elastica\Query;
use Elastica\ResultSet;
use Elastica\Search;

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

        $match = new Query\MultiMatch();
        $match->setQuery($terms);
        $match->setFields(["postalCode", "cityName"]);

        $bool = new Query\BoolQuery();
        $bool->addMust($match);

        return new Query($bool);

    }

}