<?php

namespace AppBundle\Elasticsearch;

use AppBundle\Elasticsearch\Traits\ProVehicleIndexerTrait;
use AppBundle\Elasticsearch\Builder\IndexableProVehicleBuilder;
use Novaway\ElasticsearchClient\ObjectIndexer;
use Wamcar\Vehicle\Event\VehicleEvent;
use Wamcar\Vehicle\Event\VehicleEventHandler;
use Wamcar\Vehicle\Event\VehicleRemoved;
use Wamcar\Vehicle\Event\VehicleUpdated;

class IndexUpdatedProVehicle implements VehicleEventHandler
{
    use ProVehicleIndexerTrait;

    /**
     * IndexUpdatedProVehicle constructor.
     * @param ObjectIndexer $objectIndexer
     * @param IndexableProVehicleBuilder $indexableProVehicleBuilder
     */
    public function __construct(ObjectIndexer $objectIndexer, IndexableProVehicleBuilder $indexableProVehicleBuilder)
    {
        $this->objectIndexer = $objectIndexer;
        $this->indexableProVehicleBuilder = $indexableProVehicleBuilder;
    }

    /**
     * @param VehicleEvent $event
     */
    public function notify(VehicleEvent $event)
    {
        if(!$event instanceof VehicleUpdated and !$event instanceof VehicleRemoved) {
            throw new \InvalidArgumentException("IndexUpdatedVehicle can only be notified of 'VehicleUpdated'&'VehicleRemoved' events");
        }
        $proVehicle = $event->getVehicle();

        $this->indexProVehicle($proVehicle);
    }

}
