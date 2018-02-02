<?php


namespace Wamcar\Conversation;


use Doctrine\Common\Collections\ArrayCollection;
use Ramsey\Uuid\Uuid;

class Conversation
{
    /** @var string */
    protected $id;
    /** @var ConversationUser[]|array */
    protected $conversationUsers;
    /** @var Message[]|array */
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
     * @return array|ConversationUser[]
     */
    public function getConversationUsers(): array
    {
        return $this->conversationUsers;
    }

    /**
     * @return array|Message[]
     */
    public function getMessages(): array
    {
        return $this->messages;
    }
}
