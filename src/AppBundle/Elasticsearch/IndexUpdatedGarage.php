<?php

namespace AppBundle\Elasticsearch;

use AppBundle\Elasticsearch\Traits\GarageIndexerTrait;
use Wamcar\Garage\Event\GarageEvent;
use Wamcar\Garage\Event\GarageEventHandler;
use Wamcar\Garage\Event\GarageUpdated;

class IndexUpdatedGarage implements GarageEventHandler
{
    use GarageIndexerTrait;

    /**
     * @param GarageEvent $event
     */
    public function notify(GarageEvent $event)
    {
        if (!$event instanceof GarageUpdated) {
            throw new \InvalidArgumentException("IndexUpdatedGarage can only be notified of 'GarageUpdated' events");
        }
        $this->indexUpdatedGarage($event->getGarage());
    }

}
