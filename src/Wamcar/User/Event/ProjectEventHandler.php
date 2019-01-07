<?php

namespace Wamcar\User\Event;


interface ProjectEventHandler
{
    /**
     * @param ProjectEvent $event
     */
    public function notify(ProjectEvent $event);
}
