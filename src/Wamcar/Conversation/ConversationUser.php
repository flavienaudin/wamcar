<?php


namespace Wamcar\Conversation;


use AppBundle\Services\User\CanBeInConversation;
use Doctrine\Common\Collections\ArrayCollection;
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
    /** @var Message[]|array */
    protected $messages;

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
        $this->messages = new ArrayCollection();
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

    /**
     * @return array|Message[]
     */
    public function getMessages(): array
    {
        return $this->messages;
    }
}
