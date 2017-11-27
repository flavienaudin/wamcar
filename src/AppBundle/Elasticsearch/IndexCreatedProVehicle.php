<?php

namespace AppBundle\Elasticsearch;

use AppBundle\Elasticsearch\Builder\IndexableProVehicleBuilder;
use Novaway\ElasticsearchClient\ObjectIndexer;
use Wamcar\Vehicle\Event\ProVehicleCreated;
use Wamcar\Vehicle\Event\VehicleEvent;
use Wamcar\Vehicle\Event\VehicleEventHandler;

class IndexCreatedProVehicle implements VehicleEventHandler
{
    use Traits\ProVehicleIndexerTrait;

    /**
     * IndexCreatedVehicle constructor.
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
        if(!$event instanceof ProVehicleCreated) {
            throw new \InvalidArgumentException("IndexCreatedVehicle can only be notified of 'VehicleCreated' events");
        }
        $proVehicle = $event->getVehicle();

        $this->indexProVehicle($proVehicle);
    }

}
