<?php


namespace Wamcar\Message\Event;


use Wamcar\Conversation\Message;

interface MessageEvent
{
    /**
     * MessageEvent constructor.
     * @param Message $message
     */
    public function __construct(Message $message);

    /**
     * @return Message
     */
    public function getMessage(): Message;
}
