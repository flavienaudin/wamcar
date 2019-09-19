<?php


namespace Wamcar\Conversation\Event;


interface ProContactMessageEventHandler
{

    /**
     * @param ProContactMessageEvent $event
     */
    public function notify(ProContactMessageEvent $event);
}