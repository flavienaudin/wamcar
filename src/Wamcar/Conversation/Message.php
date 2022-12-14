<?php


namespace Wamcar\Conversation;


use AppBundle\Services\User\CanBeGarageMember;
use AppBundle\Services\User\CanBeInConversation;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Wamcar\User\BaseUser;
use Wamcar\Vehicle\BaseVehicle;
use Wamcar\Vehicle\PersonalVehicle;
use Wamcar\Vehicle\ProVehicle;

class Message implements ContentWithLinkPreview
{
    /** @var int */
    protected $id;
    /** @var Conversation */
    protected $conversation;
    /** @var BaseUser Writer of the message */
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
    /** @var Collection */
    protected $linkPreviews;

    /**
     * Message constructor.
     * @param Conversation $conversation
     * @param CanBeInConversation $user
     * @param string|null $content
     * @param BaseVehicle|null $vehicleHeader
     * @param BaseVehicle|null $vehicle
     * @param bool|null $isFleet
     * @param array $attachments
     * @throws \Exception
     */
    public function __construct(
        Conversation $conversation,
        CanBeInConversation $user,
        ?string $content,
        ?BaseVehicle $vehicleHeader,
        ?BaseVehicle $vehicle,
        ?bool $isFleet,
        array $attachments
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
                if (!empty($attachment)) {
                    $this->addAttachment(new MessageAttachment($attachment, $this));
                }
            }
        }
        $this->linkPreviews = new ArrayCollection();

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
     * Get the writer of the message
     * @return null|CanBeInConversation null if user is softDeleted
     */
    public function getUser(): ?CanBeInConversation
    {
        return $this->user;
    }

    /**
     * @param BaseUser $user
     */
    public function setUser(BaseUser $user): void
    {
        $this->user = $user;
    }

    /**
     * @return array of BaseUser = Recipients this message
     */
    public function getRecipients(): array
    {
        $recipients = [];
        $this->getConversation()->getConversationUsers()->map(function (ConversationUser $conversationUser) use (&$recipients) {
            if ($conversationUser->getUser() != null && !$conversationUser->getUser()->is($this->getUser())) {
                $recipients[] = $conversationUser->getUser();
            }
        });
        return $recipients;
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
     * @return Collection
     */
    public function getLinkPreviews(): Collection
    {
        return $this->linkPreviews;
    }

    /**
     * @param LinkPreview $linkPreview
     */
    public function addLinkPreview(LinkPreview $linkPreview): void
    {
        $linkPreview->setLinkIndex($this->linkPreviews->count());
        $this->linkPreviews->add($linkPreview);
        $linkPreview->setOwner($this);
    }

    /**
     * @param LinkPreview $linkPreview
     */
    public function removeLinkPreview(LinkPreview $linkPreview): void
    {
        $this->linkPreviews->removeElement($linkPreview);
    }

    /**
     * @param BaseVehicle $vehicle
     */
    private function assignVehicleHeader(BaseVehicle $vehicle): void
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
        return $this->getPersonalVehicle() ?? $this->getProVehicle();
    }

    /**
     * @return null|BaseUser
     */
    public function getVehicleSeller(): ?BaseUser
    {
        $vehicle = $this->getVehicleHeader();
        if ($vehicle instanceof ProVehicle) {
            if ($this->user instanceof CanBeGarageMember && $this->user->isMemberOfGarage($vehicle->getGarage())) {
                return $this->user;
            } else {
                $availableSellers = $this->conversation->getConversationUsers()->filter(function (ConversationUser $conversationUser) use ($vehicle) {
                    $user = $conversationUser->getUser();
                    return $user instanceof CanBeGarageMember && $user->isMemberOfGarage($vehicle->getGarage());
                });
                return ($availableSellers->first())->getUser();
            }

        } elseif ($vehicle instanceof PersonalVehicle) {
            return $vehicle->getOwner();
        }
        return null;
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
