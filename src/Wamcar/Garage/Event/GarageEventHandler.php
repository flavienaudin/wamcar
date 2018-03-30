<?php

namespace Wamcar\Garage\Event;


interface GarageEventHandler
{

    /**
     * @param GarageEvent $event
     */
    public function notify(GarageEvent $event);

}