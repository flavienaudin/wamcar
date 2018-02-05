<?php


namespace Wamcar\Conversation;


use AppBundle\Services\User\CanBeInConversation;
use Wamcar\User\BaseUser;

class ConversationUser
{
    /** @var int */
    protected $id;
    /** @var Conversation */
    protected $conversation;
    /** @var BaseUser */
    protected $user;
    /** @var \DateTime */
    protected $lastOpenAt;

    /**
     * ConversationUser constructor.
     * @param Conversation $conversation
     * @param CanBeInConversation $user
     */
    public function __construct(Conversation $conversation, CanBeInConversation $user)
    {
        $this->conversation = $conversation;
        $this->user = $user;
        $this->lastOpenAt = new \DateTime();
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
     * @return BaseUser
     */
    public function getUser(): BaseUser
    {
        return $this->user;
    }

    /**
     * @return \DateTime
     */
    public function getLastOpenAt(): \DateTime
    {
        return $this->lastOpenAt;
    }
}
