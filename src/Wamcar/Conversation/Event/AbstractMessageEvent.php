<?php

namespace Wamcar\Conversation\Event;


use Wamcar\Conversation\Message;

abstract class AbstractMessageEvent
{
    /** @var Message */
    private $message;

    /**
     * AbstractMessageEvent constructor.
     * @param Message $message
     */
    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    /**
     * @return Message
     */
    public function getMessage(): Message
    {
        return $this->message;
    }
}
