<?php

namespace AppBundle\Elasticsearch\Traits;

use AppBundle\Elasticsearch\Builder\IndexableProVehicleBuilder;
use AppBundle\Elasticsearch\Type\IndexableProVehicle;
use Novaway\ElasticsearchClient\ObjectIndexer;
use Wamcar\Vehicle\ProVehicle;

trait ProVehicleIndexerTrait
{
    /** @var ObjectIndexer */
    private $objectIndexer;
    /** @var IndexableProVehicleBuilder */
    private $indexableProVehicleBuilder;

    protected function indexProVehicle(ProVehicle $proVehicle)
    {
        $indexableProVehicle = $this->indexableProVehicleBuilder->buildFromVehicle($proVehicle);
        if ($indexableProVehicle->shouldBeIndexed()) {
            $this->objectIndexer->index($indexableProVehicle, IndexableProVehicle::TYPE);
        } else {
            $this->objectIndexer->remove($indexableProVehicle, IndexableProVehicle::TYPE);
        }
    }
}
