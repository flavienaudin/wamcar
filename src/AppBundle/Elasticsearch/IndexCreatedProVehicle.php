<?php

namespace AppBundle\Elasticsearch;

use AppBundle\Elasticsearch\Type\IndexableProVehicle;
use AppBundle\Elasticsearch\Builder\IndexableProVehicleBuilder;
use Novaway\ElasticsearchClient\ObjectIndexer;
use Wamcar\Vehicle\Event\VehicleCreated;
use Wamcar\Vehicle\Event\VehicleEvent;
use Wamcar\Vehicle\Event\VehicleEventHandler;
use Wamcar\Vehicle\ProVehicle;

class IndexCreatedProVehicle implements VehicleEventHandler
{
    /** @var ObjectIndexer */
    private $objectIndexer;
    /** @var IndexableProVehicleBuilder */
    private $indexableProVehicleBuilder;

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
        if(!$event instanceof VehicleCreated) {
            throw new \InvalidArgumentException("IndexCreatedVehicle can only be notified of 'VehicleCreated' events");
        }
        $vehicle = $event->getVehicle();

        $this->indexVehicle($vehicle);
    }

    protected function indexVehicle(ProVehicle $vehicle)
    {
        $indexableItem = $this->indexableProVehicleBuilder->buildFromVehicle($vehicle);
        if ($indexableItem->shouldBeIndexed()) {
            $this->objectIndexer->index($indexableItem, IndexableProVehicle::TYPE);
        } else {
            $this->objectIndexer->remove($indexableItem, IndexableProVehicle::TYPE);
        }
    }

}
