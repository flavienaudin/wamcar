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
    protected $content;
    /** @var string */
    protected $vehicleHeaderId;
    /** @var \DateTime */
    protected $publishedAt;

    /**
     * Message constructor.
     * @param Conversation $conversation
     * @param CanBeInConversation $user
     * @param string $content
     * @param null|string $vehicleHeaderId
     */
    public function __construct(Conversation $conversation, CanBeInConversation $user, string $content, ?string $vehicleHeaderId = null)
    {
        $this->conversation = $conversation;
        $this->user = $user;
        $this->content = $content;
        $this->vehicleHeaderId = $vehicleHeaderId;
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
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * @return \DateTime
     */
    public function getPublishedAt(): \DateTime
    {
        return $this->publishedAt;
    }
}
