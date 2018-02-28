<?php

namespace AppBundle\Elasticsearch;

use AppBundle\Doctrine\Entity\ApplicationCity;
use AppBundle\Elasticsearch\Builder\IndexableCityBuilder;
use AppBundle\Elasticsearch\Type\IndexableCity;
use Novaway\ElasticsearchClient\ObjectIndexer;

class IndexCity
{

    /** @var ObjectIndexer */
    private $objectIndexer;
    /** @var IndexableCityBuilder */
    private $indexableCityBuilder;

    /**
     * IndexUpdatedProVehicle constructor.
     * @param ObjectIndexer $objectIndexer
     * @param IndexableCityBuilder $indexableCityBuilder
     */
    public function __construct(ObjectIndexer $objectIndexer, IndexableCityBuilder $indexableCityBuilder)
    {
        $this->objectIndexer = $objectIndexer;
        $this->indexableCityBuilder = $indexableCityBuilder;
    }

    protected function indexCity(ApplicationCity $city)
    {
        $indexableProVehicle = $this->indexableCityBuilder->buildFromApplicationCity($city);
        if ($indexableProVehicle->shouldBeIndexed()) {
            $this->objectIndexer->index($indexableProVehicle, IndexableCity::TYPE);
        } else {
            $this->objectIndexer->remove($indexableProVehicle, IndexableCity::TYPE);
        }
    }
}
