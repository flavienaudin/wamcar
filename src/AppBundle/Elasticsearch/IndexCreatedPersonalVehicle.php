<?php

namespace AppBundle\Elasticsearch;

use AppBundle\Elasticsearch\Builder\IndexablePersonalVehicleBuilder;
use Novaway\ElasticsearchClient\ObjectIndexer;
use Wamcar\Vehicle\Event\PersonalVehicleCreated;
use Wamcar\Vehicle\Event\VehicleEvent;
use Wamcar\Vehicle\Event\VehicleEventHandler;

class IndexCreatedPersonalVehicle implements VehicleEventHandler
{
    use Traits\PersonalVehicleIndexerTrait;

    /**
     * IndexCreatedVehicle constructor.
     * @param ObjectIndexer $objectIndexer
     * @param IndexablePersonalVehicleBuilder $indexablePersonalVehicleBuilder
     */
    public function __construct(ObjectIndexer $objectIndexer, IndexablePersonalVehicleBuilder $indexablePersonalVehicleBuilder)
    {
        $this->objectIndexer = $objectIndexer;
        $this->indexablePersonalVehicleBuilder = $indexablePersonalVehicleBuilder;
    }

    /**
     * @param VehicleEvent $event
     */
    public function notify(VehicleEvent $event)
    {
        if(!$event instanceof PersonalVehicleCreated) {
            throw new \InvalidArgumentException("IndexCreatedPersonalVehicle can only be notified of 'PersonalVehicleCreated' events");
        }
        $personalVehicle = $event->getVehicle();

        $this->indexPersonalVehicle($personalVehicle);
    }

}
