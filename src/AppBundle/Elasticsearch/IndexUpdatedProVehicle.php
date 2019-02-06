<?php

namespace AppBundle\Elasticsearch;

use AppBundle\Elasticsearch\Traits\ProVehicleIndexerTrait;
use Wamcar\Vehicle\Event\ProVehicleCreated;
use Wamcar\Vehicle\Event\ProVehicleRemoved;
use Wamcar\Vehicle\Event\ProVehicleUpdated;
use Wamcar\Vehicle\Event\VehicleEvent;
use Wamcar\Vehicle\Event\VehicleEventHandler;

class IndexUpdatedProVehicle implements VehicleEventHandler
{
    use ProVehicleIndexerTrait;

    /**
     * @param VehicleEvent $event
     */
    public function notify(VehicleEvent $event)
    {
        if (!$event instanceof ProVehicleCreated && !$event instanceof ProVehicleUpdated && !$event instanceof ProVehicleRemoved) {
            throw new \InvalidArgumentException("IndexUpdatedVehicle can only be notified of 'ProVehicleCreated', 'ProVehicleUpdated' or 'ProVehicleRemoved' events");
        }
        $this->indexProVehicle($event->getVehicle());
    }

}
