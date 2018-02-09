<?php


namespace Wamcar\Conversation;


use AppBundle\Services\User\CanBeInConversation;
use Wamcar\User\BaseUser;
use Wamcar\Vehicle\BaseVehicle;
use Wamcar\Vehicle\PersonalVehicle;
use Wamcar\Vehicle\ProVehicle;

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
    /** @var null|ProVehicle */
    protected $proVehicleHeader;
    /** @var null|PersonalVehicle */
    protected $personalVehicleHeader;
    /** @var \DateTime */
    protected $publishedAt;

    /**
     * Message constructor.
     * @param Conversation $conversation
     * @param CanBeInConversation $user
     * @param string $content
     * @param null|ProVehicle $proVehicleHeader
     * @param null|PersonalVehicle $personalVehicleHeader
     */
    public function __construct(
        Conversation $conversation,
        CanBeInConversation $user,
        string $content,
        ?ProVehicle $proVehicleHeader = null,
        ?PersonalVehicle $personalVehicleHeader = null
    )
    {
        $this->conversation = $conversation;
        $this->user = $user;
        $this->content = $content;
        $this->proVehicleHeader = $proVehicleHeader;
        $this->personalVehicleHeader = $personalVehicleHeader;
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

    /**
     * @return null|ProVehicle
     */
    public function getProVehicleHeader(): ?ProVehicle
    {
        return $this->proVehicleHeader;
    }

    /**
     * @return null|PersonalVehicle
     */
    public function getPersonalVehicleHeader(): ?PersonalVehicle
    {
        return $this->personalVehicleHeader;
    }

    /**
     * @return null|BaseVehicle
     */
    public function getVehicleHeader(): ?BaseVehicle
    {
        if ($this->getPersonalVehicleHeader()) {
            return $this->getPersonalVehicleHeader();
        }

        return $this->getProVehicleHeader();
    }
}
