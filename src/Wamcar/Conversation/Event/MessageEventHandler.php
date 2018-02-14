<?php

namespace Wamcar\Message\Event;


interface MessageEventHandler
{
    /**
     * @param MessageEvent $event
     */
    public function notify(MessageEvent $event);
}
