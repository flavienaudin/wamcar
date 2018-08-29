<?php

namespace Wamcar\User\Event;


interface LikeVehicleEventHandler
{
    /**
     * @param LikeVehicleEvent $event
     */
    public function notify(LikeVehicleEvent $event);
}