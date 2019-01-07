<?php


namespace Wamcar\Conversation;


use AppBundle\Services\User\CanBeInConversation;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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
    /** @var Collection|array */
    protected $attachments;

    /**
     * Message constructor.
     * @param Conversation $conversation
     * @param CanBeInConversation $user
     * @param string $content
     * @param null|BaseVehicle $vehicleHeader
     * @param null|BaseVehicle $vehicle
     * @param bool|null $isFleet
     * @param array|Collection $attachments
     */
    public function __construct(
        Conversation $conversation,
        CanBeInConversation $user,
        ?string $content = '',
        ?BaseVehicle $vehicleHeader = null,
        ?BaseVehicle $vehicle = null,
        ?bool $isFleet = false,
        $attachments = null
    )
    {
        $this->conversation = $conversation;
        $this->user = $user;
        $this->content = $content ?? '';
        $this->isFleet = $isFleet;
        $this->publishedAt = new \DateTime();
        $this->attachments = new ArrayCollection();
        if ($attachments != null) {
            foreach ($attachments as $attachment) {
                if(!empty($attachment)) {
                    $this->addAttachment(new MessageAttachment(null, $attachment, $this));
                }
            }
        }

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
     * @return Collection
     */
    public function getAttachments(): Collection
    {
        return $this->attachments;
    }

    /**
     * @param MessageAttachment $attachment
     */
    public function addAttachment(MessageAttachment $attachment): void
    {
        if ($attachment->getId()) {
            $this->attachments[] = $attachment;
        }
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

    public function removeVehicleHeader(): void
    {
        $this->proVehicleHeader = null;
        $this->personalVehicleHeader = null;
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

    public function removeVehicle(): void
    {
        $this->proVehicle = null;
        $this->personalVehicle = null;
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
