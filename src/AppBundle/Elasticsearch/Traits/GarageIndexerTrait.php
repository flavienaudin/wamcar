<?php

namespace AppBundle\Elasticsearch\Traits;

use AppBundle\Elasticsearch\Builder\IndexableProVehicleBuilder;
use AppBundle\Elasticsearch\Type\IndexableProVehicle;
use Novaway\ElasticsearchClient\ObjectIndexer;
use Wamcar\Garage\Garage;
use Wamcar\Vehicle\ProVehicle;

trait GarageIndexerTrait
{
    /** @var ObjectIndexer */
    private $objectIndexer;
    /** @var IndexableProVehicleBuilder */
    private $indexableProVehicleBuilder;

    protected function indexUpdatedGarage(Garage $garage)
    {
        /** @var ProVehicle $vehicle */
        foreach ($garage->getProVehicles() as $vehicle) {
            $indexableProVehicle = $this->indexableProVehicleBuilder->buildFromVehicle($vehicle);

            if ($indexableProVehicle->shouldBeIndexed()) {
                $this->objectIndexer->index($indexableProVehicle, IndexableProVehicle::TYPE);
            } else {
                $this->objectIndexer->remove($indexableProVehicle, IndexableProVehicle::TYPE);
            }
        }
    }
}
