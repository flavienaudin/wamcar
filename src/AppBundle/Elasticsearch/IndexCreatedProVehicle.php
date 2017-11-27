<?php

namespace AppBundle\Elasticsearch;

use AppBundle\Elasticsearch\Builder\IndexableProVehicleBuilder;
use AppBundle\Elasticsearch\Traits\ProVehicleIndexerTrait;
use Novaway\ElasticsearchClient\ObjectIndexer;
use Wamcar\Vehicle\Event\ProVehicleCreated;
use Wamcar\Vehicle\Event\VehicleEvent;
use Wamcar\Vehicle\Event\VehicleEventHandler;

class IndexCreatedProVehicle implements VehicleEventHandler
{
    use ProVehicleIndexerTrait;

    /**
     * IndexCreatedProVehicle constructor.
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
            throw new \InvalidArgumentException("IndexCreatedProVehicle can only be notified of 'ProVehicleCreated' events");
        }
        $proVehicle = $event->getVehicle();

        $this->indexProVehicle($proVehicle);
    }

}
