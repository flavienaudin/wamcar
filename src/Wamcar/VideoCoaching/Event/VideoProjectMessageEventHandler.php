<?php


namespace Wamcar\VideoCoaching\Event;


interface VideoProjectMessageEventHandler
{

    /**
     * @param VideoProjectMessageEvent $event
     */
    public function notify(VideoProjectMessageEvent $event);
}