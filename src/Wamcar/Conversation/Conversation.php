<?php


namespace Wamcar\Conversation;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Ramsey\Uuid\Uuid;

class Conversation
{
    /** @var string */
    protected $id;
    /** @var ConversationUser[]|Collection */
    protected $conversationUsers;
    /** @var Message[]|Collection */
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
     * @return Collection|ConversationUser[]
     */
    public function getConversationUsers(): array
    {
        return $this->conversationUsers;
    }

    /**
     * @return Collection|Message[]
     */
    public function getMessages(): array
    {
        return $this->messages;
    }
}
