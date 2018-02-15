<?php


namespace Wamcar\Conversation\Event;


use Wamcar\Conversation\Message;
use Wamcar\User\BaseUser;

interface MessageEvent
{
    /**
     * MessageEvent constructor.
     * @param Message $message
     * @param BaseUser $interlocutor
     */
    public function __construct(Message $message, BaseUser $interlocutor, ?string $pathImg = null);

    /**
     * @return Message
     */
    public function getMessage(): Message;

    /**
     * @return BaseUser
     */
    public function getInterlocutor(): BaseUser;

    /**
     * @return null|string
     */
    public function getPathImg(): ?string;
}
