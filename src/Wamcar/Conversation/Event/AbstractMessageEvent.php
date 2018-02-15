<?php

namespace Wamcar\Conversation\Event;


use Wamcar\Conversation\Message;
use Wamcar\User\BaseUser;

abstract class AbstractMessageEvent
{
    /** @var Message */
    private $message;
    /** @var BaseUser */
    private $interlocutor;
    /** @var string */
    private $pathImg;

    /**
     * AbstractMessageEvent constructor.
     * @param Message $message
     * @param BaseUser $interlocutor
     */
    public function __construct(Message $message, BaseUser $interlocutor, ?string $pathImg = null)
    {
        $this->message = $message;
        $this->interlocutor = $interlocutor;
        $this->pathImg = $pathImg;
    }

    /**
     * @return Message
     */
    public function getMessage(): Message
    {
        return $this->message;
    }

    /**
     * @return BaseUser
     */
    public function getInterlocutor(): BaseUser
    {
        return $this->interlocutor;
    }

    /**
     * @return null|string
     */
    public function getPathImg(): ?string
    {
        return $this->pathImg;
    }
}
