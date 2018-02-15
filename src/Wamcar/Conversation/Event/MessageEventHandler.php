<?php

namespace Wamcar\Conversation\Event;


interface MessageEventHandler
{
    /**
     * @param MessageEvent $event
     */
    public function notify(MessageEvent $event);
}
