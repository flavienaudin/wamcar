<?php

namespace Wamcar\User\Event;


interface UserEventHandler
{
    /**
     * @param UserEvent $event
     */
    public function notify(UserEvent $event);
}
