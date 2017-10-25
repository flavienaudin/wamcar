<?php

namespace AppBundle\MailWorkflow;

abstract class AbstractEmailEventHandler
{
    use Traits\EmailSender;

    protected function checkEventClass($event, $className)
    {
        if (!$event instanceof $className) {
            throw new \InvalidArgumentException(static::class ." can only be notified of '$className' events");
        }
    }

}
