<?php


namespace Wamcar\User\Event;


interface LeadEventHandler
{

    /**
     * @param LeadEvent $event
     */
    public function notify(LeadEvent $event);
}