<?php

namespace AppBundle\Elasticsearch;

use AppBundle\Elasticsearch\Builder\IndexableProVehicleBuilder;
use AppBundle\Elasticsearch\Traits\GarageIndexerTrait;
use Novaway\ElasticsearchClient\ObjectIndexer;
use Wamcar\Garage\Event\GarageEvent;
use Wamcar\Garage\Event\GarageEventHandler;
use Wamcar\Garage\Event\GarageUpdated;

class IndexUpdatedGarage implements GarageEventHandler
{
    use GarageIndexerTrait;

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
     * @param GarageEvent $event
     */
    public function notify(GarageEvent $event)
    {
        if (!$event instanceof GarageUpdated) {
            throw new \InvalidArgumentException("IndexUpdatedGarage can only be notified of 'GarageUpdated' events");
        }
        $garage = $event->getGarage();

        $this->indexUpdatedGarage($garage);
    }

}
