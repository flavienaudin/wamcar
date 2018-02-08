<?php


namespace Wamcar\Conversation;


use AppBundle\Doctrine\Entity\ApplicationConversation;
use AppBundle\Services\User\CanBeInConversation;
use mageekguy\atoum\tests\units\asserters\boolean;
use Wamcar\User\BaseUser;

class ConversationUser
{
    /** @var int */
    protected $id;
    /** @var ApplicationConversation */
    protected $conversation;
    /** @var BaseUser */
    protected $user;
    /** @var \DateTime */
    protected $lastOpenedAt;

    /**
     * ConversationUser constructor.
     * @param Conversation $conversation
     * @param CanBeInConversation $user
     */
    public function __construct(Conversation $conversation, CanBeInConversation $user)
    {
        $this->conversation = $conversation;
        $this->user = $user;
        $this->lastOpenedAt = new \DateTime();
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return ApplicationConversation
     */
    public function getConversation(): ApplicationConversation
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
    public function getLastOpenedAt(): \DateTime
    {
        return $this->lastOpenedAt;
    }

    /**
     * @param \DateTime $lastOpenedAt
     */
    public function setLastOpenedAt(\DateTime $lastOpenedAt): void
    {
        $this->lastOpenedAt = $lastOpenedAt;
    }

    /**
     * @return bool
     */
    public function hasUnreadMessages(): bool
    {
        return $this->getLastOpenedAt() < $this->getConversation()->getUpdatedAt();
    }
}
