<?php

namespace GoogleApi\Event;


interface GoogleApiReturnErrorEventHandler
{

    /**
     * @param GoogleApiReturnErrorEvent $event
     */
    public function notify(GoogleApiReturnErrorEvent $event);

}