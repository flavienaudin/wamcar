<?php

namespace Wamcar\Garage\Event;


interface PendingRequestToJoinGarageEventHandler
{

    /**
     * @param PendingRequestToJoinGarageEvent $event
     */
    public function notify(PendingRequestToJoinGarageEvent $event);
}