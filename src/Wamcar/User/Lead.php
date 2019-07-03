<?php

namespace Wamcar\User;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Wamcar\Sale\Declaration;
use Wamcar\User\Enum\LeadInitiatedBy;
use Wamcar\User\Enum\LeadStatus;

class Lead
{
    /** @var int */
    private $id;
    /** @var ProUser */
    private $proUser;
    /** @var LeadStatus */
    private $status;
    /** @var null|BaseUser */
    private $userLead;
    /** @var string */
    private $firstName;
    /** @var null|string */
    private $lastName;
    /** @var \DateTimeInterface */
    private $createdAt;
    /** @var LeadInitiatedBy */
    private $initiatedBy;
    /** @var \DateTimeInterface */
    private $lastContactedAt;
    /** @var int */
    private $nbPhoneActionByLead;
    /** @var int */
    private $nbPhoneProActionByLead;
    /** @var int */
    private $nbLeadMessages;
    /** @var int */
    private $nbLeadLikes;
    /** @var int */
    private $nbPhoneActionByPro;
    /** @var int */
    private $nbPhoneProActionByPro;
    /** @var int */
    private $nbProMessages;
    /** @var int */
    private $nbProLikes;
    /** @var Collection */
    protected $saleDeclarations;


    /**
     * Lead constructor.
     * @param ProUser $proUser
     * @param null|BaseUser $initiator
     * @param null|BaseUser $userLead
     */
    public function __construct(ProUser $proUser, ?BaseUser $initiator = null, ?BaseUser $userLead = null)
    {
        $this->proUser = $proUser;
        $proUser->addLead($this);
        if ($proUser->is($initiator)) {
            $this->initiatedBy = LeadInitiatedBy::PRO_USER();
        } else {
            $this->initiatedBy = LeadInitiatedBy::LEAD();
        }
        $this->status = LeadStatus::TO_QUALIFY();
        $this->userLead = $userLead;
        if ($userLead != null) {
            $this->firstName = $userLead->getFirstName();
            $this->lastName = $userLead->getLastName();
        }
        $this->nbPhoneActionByLead = 0;
        $this->nbPhoneProActionByLead = 0;
        $this->nbLeadMessages = 0;
        $this->nbLeadLikes = 0;

        $this->nbPhoneActionByPro = 0;
        $this->nbPhoneProActionByPro = 0;
        $this->nbProMessages = 0;
        $this->nbProLikes = 0;

        $this->saleDeclarations = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return ProUser
     */
    public function getProUser(): ProUser
    {
        return $this->proUser;
    }

    /**
     * @param ProUser $proUser
     */
    public function setProUser(ProUser $proUser): void
    {
        $this->proUser = $proUser;
    }

    /**
     * @return LeadStatus
     */
    public function getStatus(): LeadStatus
    {
        return $this->status;
    }

    /**
     * @param LeadStatus $status
     */
    public function setStatus(LeadStatus $status): void
    {
        $this->status = $status;
    }

    /**
     * @return null|BaseUser
     */
    public function getUserLead(): ?BaseUser
    {
        return $this->userLead;
    }

    /**
     * @param null|BaseUser $userLead
     */
    public function setUserLead(?BaseUser $userLead): void
    {
        $this->userLead = $userLead;
    }


    /**
     * @return string
     */
    public function getFullName(): string
    {
        if($this->userLead != null){
            return $this->userLead->getFullName();
        }else {
            return join(' ', [$this->firstName, $this->lastName]);
        }
    }

    /**
     * @return string
     */
    public function getFirstName(): string
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     */
    public function setFirstName(string $firstName): void
    {
        $this->firstName = $firstName;
    }

    /**
     * @return null|string
     */
    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    /**
     * @param null|string $lastName
     */
    public function setLastName(?string $lastName): void
    {
        $this->lastName = $lastName;
    }

    /**
     * @return null|\DateTimeInterface
     */
    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTimeInterface $createdAt
     */
    public function setCreatedAt(\DateTimeInterface $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return LeadInitiatedBy
     */
    public function getInitiatedBy(): LeadInitiatedBy
    {
        return $this->initiatedBy;
    }

    /**
     * @param LeadInitiatedBy $initiatedBy
     */
    public function setInitiatedBy(LeadInitiatedBy $initiatedBy): void
    {
        $this->initiatedBy = $initiatedBy;
    }

    /**
     * @return null|\DateTimeInterface
     */
    public function getLastContactedAt(): ?\DateTimeInterface
    {
        return $this->lastContactedAt;
    }

    /**
     * @param \DateTimeInterface $lastContactedAt
     */
    public function setLastContactedAt(\DateTimeInterface $lastContactedAt): void
    {
        $this->lastContactedAt = $lastContactedAt;
    }

    /**
     * @return int
     */
    public function getNbPhoneActionByLead(): int
    {
        return $this->nbPhoneActionByLead;
    }

    /**
     * @param int $nbPhoneActionByLead
     */
    public function setNbPhoneActionByLead(int $nbPhoneActionByLead): void
    {
        $this->nbPhoneActionByLead = $nbPhoneActionByLead;
    }

    /**
     * @param int|null $nbPhoneActionByLead
     */
    public function increaseNbPhoneActionByLead(?int $nbPhoneActionByLead = 1): void
    {
        $this->nbPhoneActionByLead = max($this->nbPhoneActionByLead + $nbPhoneActionByLead, 0);
    }

    /**
     * @return int
     */
    public function getNbPhoneProActionByLead(): int
    {
        return $this->nbPhoneProActionByLead;
    }

    /**
     * @param int $nbPhoneProActionByLead
     */
    public function setNbPhoneProActionByLead(int $nbPhoneProActionByLead): void
    {
        $this->nbPhoneProActionByLead = $nbPhoneProActionByLead;
    }

    /**
     * @param int|null $nbPhoneProActionByLead
     */
    public function increaseNbPhoneProActionByLead(?int $nbPhoneProActionByLead = 1): void
    {
        $this->nbPhoneProActionByLead = max($this->nbPhoneProActionByLead + $nbPhoneProActionByLead, 0);
    }

    /**
     * @return int
     */
    public function getNbLeadMessages(): int
    {
        return $this->nbLeadMessages;
    }

    /**
     * @param int $nbLeadMessages
     */
    public function setNbLeadMessages(int $nbLeadMessages): void
    {
        $this->nbLeadMessages = $nbLeadMessages;
    }

    /**
     * @param int|null $nbLeadMessages
     */
    public function increaseNbLeadMessages(?int $nbLeadMessages = 1): void
    {
        $this->nbLeadMessages = max($this->nbLeadMessages + $nbLeadMessages, 0);
    }

    /**
     * @return int
     */
    public function getNbLeadLikes(): int
    {
        return $this->nbLeadLikes;
    }

    /**
     * @param int $nbLeadLikes
     */
    public function setNbLeadLikes(int $nbLeadLikes): void
    {
        $this->nbLeadLikes = $nbLeadLikes;
    }

    /**
     * @param int|null $nbLeadLikes can be negative
     */
    public function increaseNbLeadLikes(?int $nbLeadLikes = 1): void
    {
        $this->nbLeadLikes = max($this->nbLeadLikes + $nbLeadLikes, 0);
    }

    /**
     * @return int
     */
    public function getNbPhoneActionByPro(): int
    {
        return $this->nbPhoneActionByPro;
    }

    /**
     * @param int $nbPhoneActionByPro
     */
    public function setNbPhoneActionByPro(int $nbPhoneActionByPro): void
    {
        $this->nbPhoneActionByPro = $nbPhoneActionByPro;
    }

    /**
     * @param int|null $nbPhoneActionByPro
     */
    public function increaseNbPhoneActionByPro(?int $nbPhoneActionByPro = 1): void
    {
        $this->nbPhoneActionByPro = max($this->nbPhoneActionByPro + $nbPhoneActionByPro, 0);
    }

    /**
     * @return int
     */
    public function getNbPhoneProActionByPro(): int
    {
        return $this->nbPhoneProActionByPro;
    }

    /**
     * @param int $nbPhoneProActionByPro
     */
    public function setNbPhoneProActionByPro(int $nbPhoneProActionByPro): void
    {
        $this->nbPhoneProActionByPro = $nbPhoneProActionByPro;
    }

    /**
     * @param int|null $nbPhoneProActionByPro
     */
    public function increaseNbPhoneProActionByPro(?int $nbPhoneProActionByPro = 1): void
    {
        $this->nbPhoneProActionByPro = max($this->nbPhoneProActionByPro + $nbPhoneProActionByPro, 0);
    }

    /**
     * @return int
     */
    public function getNbProMessages(): int
    {
        return $this->nbProMessages;
    }

    /**
     * @param int $nbProMessages
     */
    public function setNbProMessages(int $nbProMessages): void
    {
        $this->nbProMessages = $nbProMessages;
    }

    /**
     * @param int|null $nbProMessages can be negative
     */
    public function increaseNbProMessages(?int $nbProMessages = 1): void
    {
        $this->nbProMessages = max($this->nbProMessages + $nbProMessages, 0);
    }

    /**
     * @return int
     */
    public function getNbProLikes(): int
    {
        return $this->nbProLikes;
    }

    /**
     * @param int $nbProLikes
     */
    public function setNbProLikes(int $nbProLikes): void
    {
        $this->nbProLikes = $nbProLikes;
    }

    /**
     * @param int|null $nbProLikes can be negative
     */
    public function increaseNbProLikes(?int $nbProLikes = 1): void
    {
        $this->nbProLikes = max($this->nbProLikes + $nbProLikes, 0);
    }

    /**
     * @return Collection
     */
    public function getSaleDeclarations(): Collection
    {
        return $this->saleDeclarations;
    }

    /**
     * @param Declaration $declaration
     */
    public function addSaleDeclaration(Declaration $declaration): void
    {
        $this->saleDeclarations->add($declaration);
    }

    /**
     * @param Declaration $declaration
     */
    public function removeSaleDeclaration(Declaration $declaration): void
    {
        $this->saleDeclarations->add($declaration);
    }
}