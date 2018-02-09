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
     * @param null|BaseVehicle $vehicle
     */
    public function __construct(
        Conversation $conversation,
        CanBeInConversation $user,
        string $content,
        ?BaseVehicle $vehicle = null
    )
    {
        $this->conversation = $conversation;
        $this->user = $user;
        $this->content = $content;
        $this->publishedAt = new \DateTime();
        if ($vehicle) {
            $this->assignVehicleHeader($vehicle);
        }
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
    protected function getProVehicleHeader(): ?ProVehicle
    {
        return $this->proVehicleHeader;
    }

    /**
     * @return null|PersonalVehicle
     */
    protected function getPersonalVehicleHeader(): ?PersonalVehicle
    {
        return $this->personalVehicleHeader;
    }

    /**
     * @return null|BaseVehicle
     */
    public function getVehicleHeader(): ?BaseVehicle
    {
        return $this->getPersonalVehicleHeader() ?: $this->getProVehicleHeader();
    }

    /**
     * @param BaseVehicle $vehicle
     */
    public function assignVehicleHeader(BaseVehicle $vehicle): void
    {
        if ($vehicle instanceof ProVehicle) {
            $this->proVehicleHeader = $vehicle;
        } elseif ($vehicle instanceof PersonalVehicle) {
            $this->personalVehicleHeader = $vehicle;
        }
    }
}
