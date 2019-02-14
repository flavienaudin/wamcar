<?php

namespace AppBundle\Elasticsearch\Elastica;


use Elastica\ResultSet;

class ElasticUtils
{

    public static function numberOfPages(ResultSet $resultSet): int
    {
        $size = $resultSet->getQuery()->getParam("size");
        if ($size <= 0) {
            throw new \InvalidArgumentException("limit parameter must be strictly positive, $size given");
        }
        $totalHits = $resultSet->getTotalHits();
        return ceil($totalHits / $size);
    }

}