<?php


namespace Wamcar\Conversation;


class Message
{
    /** @var int */
    protected $id;
    /** @var Conversation */
    protected $conversation;
    /** @var ConversationUser */
    protected $conversationUser;
    /** @var string */
    protected $message;
    /** @var \DateTime */
    protected $publishedAt;

    /**
     * Message constructor.
     * @param Conversation $conversation
     * @param $conversationUser
     * @param string $message
     */
    public function __construct(Conversation $conversation,  $conversationUser, string $message)
    {
        $this->conversation = $conversation;
        $this->conversationUser = $conversationUser;
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
     * @return ConversationUser
     */
    public function getConversationUser(): ConversationUser
    {
        return $this->conversationUser;
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
