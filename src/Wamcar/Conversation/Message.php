<?php


namespace Wamcar\Conversation;


use AppBundle\Services\User\CanBeInConversation;
use Wamcar\User\BaseUser;

class Message
{
    /** @var int */
    protected $id;
    /** @var Conversation */
    protected $conversation;
    /** @var BaseUser */
    protected $user;
    /** @var string */
    protected $message;
    /** @var \DateTime */
    protected $publishedAt;

    /**
     * Message constructor.
     * @param Conversation $conversation
     * @param CanBeInConversation $user
     * @param string $message
     */
    public function __construct(Conversation $conversation, CanBeInConversation $user, string $message)
    {
        $this->conversation = $conversation;
        $this->user = $user;
        $this->message = $message;
        $this->publishedAt = new \DateTime();
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return Conversation
     */
    public function getConversation(): Conversation
    {
        return $this->conversation;
    }

    /**
     * @return CanBeInConversation
     */
    public function getUser(): CanBeInConversation
    {
        return $this->user;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @return \DateTime
     */
    public function getPublishedAt(): \DateTime
    {
        return $this->publishedAt;
    }
}
