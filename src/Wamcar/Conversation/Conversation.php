<?php


namespace Wamcar\Conversation;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Ramsey\Uuid\Uuid;

class Conversation
{
    /** @var string */
    protected $id;
    /** @var Collection <ConversationUser> */
    protected $conversationUsers;
    /** @var Collection <Message> */
    protected $messages;

    public function __construct()
    {
        $this->id = Uuid::uuid4();
        $this->conversationUsers = new ArrayCollection();
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
     * @return Collection <ConversationUser>
     */
    public function getConversationUsers(): Collection
    {
        return $this->conversationUsers;
    }

    /**
     * @param ConversationUser $conversationUser
     * @return Collection <ConversationUser>
     */
    public function addConversationUser(ConversationUser $conversationUser): Collection
    {
        if (!$this->conversationUsers->contains($conversationUser)) {
            $this->conversationUsers->add($conversationUser);
        }

        return $this->conversationUsers;
    }

    /**
     * @return Collection <Message>
     */
    public function getMessages(): Collection
    {
        return $this->messages;
    }

    /**
     * @param Message $message
     * @return Collection <Message>
     */
    public function addMessage(Message $message): Collection
    {
        if (!$this->messages->contains($message)) {
            $this->messages->add($message);
        }

        return $this->messages;
    }
}
