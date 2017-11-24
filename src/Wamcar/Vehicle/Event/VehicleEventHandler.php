<?php

namespace Wamcar\Vehicle\Event;


interface VehicleEventHandler
{
    /**
     * @param VehicleEvent $event
     */
    public function notify(VehicleEvent $event);
}
