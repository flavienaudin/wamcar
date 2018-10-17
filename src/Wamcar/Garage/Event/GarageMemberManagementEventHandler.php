<?php

namespace Wamcar\Garage\Event;


interface GarageMemberManagementEventHandler
{

    /**
     * @param GarageMemberManagementEvent $event
     */
    public function notify(GarageMemberManagementEvent $event);
}