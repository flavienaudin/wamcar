<?php

namespace AppBundle\Elasticsearch;

use AppBundle\Elasticsearch\Builder\IndexablePersonalVehicleBuilder;
use AppBundle\Elasticsearch\Traits\PersonalVehicleIndexerTrait;
use Novaway\ElasticsearchClient\ObjectIndexer;
use Wamcar\Vehicle\Event\PersonalVehicleRemoved;
use Wamcar\Vehicle\Event\PersonalVehicleUpdated;
use Wamcar\Vehicle\Event\VehicleEvent;
use Wamcar\Vehicle\Event\VehicleEventHandler;

class IndexUpdatedPersonalVehicle implements VehicleEventHandler
{
    use PersonalVehicleIndexerTrait;

    /**
     * IndexUpdatedPersonalVehicle constructor.
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
        if (!$event instanceof PersonalVehicleUpdated && !$event instanceof PersonalVehicleRemoved) {
            throw new \InvalidArgumentException("IndexUpdatedPersonalVehicle can only be notified of 'PersonalVehicleUpdated'&'PersonalVehicleRemoved' events");
        }
        $personalVehicle = $event->getVehicle();

        $this->indexPersonalVehicle($personalVehicle);
    }

}
