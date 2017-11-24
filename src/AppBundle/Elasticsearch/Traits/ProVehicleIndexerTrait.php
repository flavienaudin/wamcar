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

    protected function indexProVehicle(ProVehicle $vehicle)
    {
        $indexableItem = $this->indexableProVehicleBuilder->buildFromVehicle($vehicle);
        if ($indexableItem->shouldBeIndexed()) {
            $this->objectIndexer->index($indexableItem, IndexableProVehicle::TYPE);
        } else {
            $this->objectIndexer->remove($indexableItem, IndexableProVehicle::TYPE);
        }
    }
}
