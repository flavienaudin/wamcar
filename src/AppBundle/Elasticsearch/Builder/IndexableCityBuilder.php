<?php

namespace AppBundle\Elasticsearch\Builder;

use AppBundle\Doctrine\Entity\ApplicationCity;
use AppBundle\Elasticsearch\Type\IndexableCity;

class IndexableCityBuilder
{

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

}
