<?php

namespace AppBundle\Elasticsearch;

use AppBundle\Elasticsearch\Traits\PersonalVehicleIndexerTrait;
use Wamcar\Vehicle\Event\PersonalVehicleCreated;
use Wamcar\Vehicle\Event\PersonalVehicleRemoved;
use Wamcar\Vehicle\Event\PersonalVehicleUpdated;
use Wamcar\Vehicle\Event\VehicleEvent;
use Wamcar\Vehicle\Event\VehicleEventHandler;
use Wamcar\Vehicle\PersonalVehicle;

class IndexUpdatedPersonalVehicle implements VehicleEventHandler
{
    use PersonalVehicleIndexerTrait;

    /**
     * @param VehicleEvent $event
     */
    public function notify(VehicleEvent $event)
    {
        if (!$event instanceof PersonalVehicleCreated && !$event instanceof PersonalVehicleUpdated && !$event instanceof PersonalVehicleRemoved) {
            throw new \InvalidArgumentException("IndexUpdatedPersonalVehicle can only be notified of 'PersonalVehicleCreated', 'PersonalVehicleUpdated' or 'PersonalVehicleRemoved' events");
        }
        /** @var PersonalVehicle $personalVehicle */
        $personalVehicle = $event->getVehicle();

        $this->indexPersonalVehicle($personalVehicle);
        $this->indexPersonalUserSearchItems($personalVehicle->getOwner(), $event);
    }

}
