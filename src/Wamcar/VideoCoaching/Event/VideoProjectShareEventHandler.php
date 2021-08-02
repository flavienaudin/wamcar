<?php


namespace Wamcar\VideoCoaching\Event;


interface VideoProjectShareEventHandler
{

    /**
     * @param VideoProjectShareEvent $event
     */
    public function notify(VideoProjectShareEvent $event);
}