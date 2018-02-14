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
    //Vehicle Header = vehicle message referer
    /** @var null|ProVehicle */
    protected $proVehicleHeader;
    /** @var null|PersonalVehicle */
    protected $personalVehicleHeader;
    //Vehicle = vehicle added in message manually
    /** @var null|ProVehicle */
    protected $proVehicle;
    /** @var null|PersonalVehicle */
    protected $personalVehicle;
    /** @var bool */
    protected $isFleet;
    /** @var \DateTime */
    protected $publishedAt;

    /**
     * Message constructor.
     * @param Conversation $conversation
     * @param CanBeInConversation $user
     * @param string $content
     * @param null|BaseVehicle $vehicleHeader
     * @param null|BaseVehicle $vehicle
     * @param bool|null $isFleet
     */
    public function __construct(
        Conversation $conversation,
        CanBeInConversation $user,
        string $content,
        ?BaseVehicle $vehicleHeader = null,
        ?BaseVehicle $vehicle = null,
        ?bool $isFleet = false
    )
    {
        $this->conversation = $conversation;
        $this->user = $user;
        $this->content = $content;
        $this->isFleet = $isFleet;
        $this->publishedAt = new \DateTime();
        if ($vehicleHeader) {
            $this->assignVehicleHeader($vehicleHeader);
        }
        if ($vehicle) {
            $this->assignVehicle($vehicle);
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
     * @return bool
     */
    public function isFleet(): bool
    {
        return $this->isFleet;
    }

    /**
     * @return \DateTime
     */
    public function getPublishedAt(): \DateTime
    {
        return $this->publishedAt;
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
    public function getVehicle(): ?BaseVehicle
    {
        return $this->getPersonalVehicle() ?: $this->getProVehicle();
    }

    /**
     * @param BaseVehicle $vehicle
     */
    public function assignVehicle(BaseVehicle $vehicle): void
    {
        if ($vehicle instanceof ProVehicle) {
            $this->proVehicle = $vehicle;
        } elseif ($vehicle instanceof PersonalVehicle) {
            $this->personalVehicle = $vehicle;
        }
    }

    /**
     * @return null|ProVehicle
     */
    protected function getProVehicle(): ?ProVehicle
    {
        return $this->proVehicle;
    }

    /**
     * @return null|PersonalVehicle
     */
    protected function getPersonalVehicle(): ?PersonalVehicle
    {
        return $this->personalVehicle;
    }
}
