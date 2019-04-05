<?php

namespace Wamcar\User;


class Lead
{
    /** @var int */
    private $id;
    /** @var ProUser */
    private $proUser;
    /** @var null|BaseUser */
    private $userLead;
    /** @var string */
    private $firstName;
    /** @var null|string */
    private $lastName;
    /** @var \DateTime */
    private $lastContactedAt;
    /** @var int */
    private $nbPhoneAction;
    /** @var int */
    private $nbPhoneProAction;
    /** @var int */
    private $nbMessages;
    /** @var int */
    private $nbLikes;

    /**
     * Lead constructor.
     * @param ProUser $proUser
     * @param null|BaseUser $userLead
     */
    public function __construct(ProUser $proUser, ?BaseUser $userLead = null)
    {
        $this->proUser = $proUser;
        $this->userLead = $userLead;
        if ($userLead != null) {
            $this->firstName = $userLead->getFirstName();
            $this->lastName = $userLead->getLastName();
        }
        $this->lastContactedAt = new \DateTime();
        $this->nbPhoneAction = 0;
        $this->nbPhoneProAction = 0;
        $this->nbMessages = 0;
        $this->nbLikes = 0;
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
     * @return \DateTime
     */
    public function getLastContactedAt(): \DateTime
    {
        return $this->lastContactedAt;
    }

    /**
     * @param \DateTime $lastContactedAt
     */
    public function setLastContactedAt(\DateTime $lastContactedAt): void
    {
        $this->lastContactedAt = $lastContactedAt;
    }

    /**
     * @return int
     */
    public function getNbPhoneAction(): int
    {
        return $this->nbPhoneAction;
    }

    /**
     * @param int $nbPhoneAction
     */
    public function setNbPhoneAction(int $nbPhoneAction): void
    {
        $this->nbPhoneAction = $nbPhoneAction;
    }

    /**
     * @param int|null $nbPhoneAction
     */
    public function increaseNbPhoneAction(?int $nbPhoneAction = 1): void
    {
        $this->nbPhoneAction += $nbPhoneAction;
    }

    /**
     * @return int
     */
    public function getNbPhoneProAction(): int
    {
        return $this->nbPhoneProAction;
    }

    /**
     * @param int $nbPhoneProAction
     */
    public function setNbPhoneProAction(int $nbPhoneProAction): void
    {
        $this->nbPhoneProAction = $nbPhoneProAction;
    }

    /**
     * @param int|null $nbPhoneProAction
     */
    public function increaseNbPhoneProAction(?int $nbPhoneProAction = 1): void
    {
        $this->nbPhoneProAction += $nbPhoneProAction;
    }

    /**
     * @return int
     */
    public function getNbMessages(): int
    {
        return $this->nbMessages;
    }

    /**
     * @param int $nbMessages
     */
    public function setNbMessages(int $nbMessages): void
    {
        $this->nbMessages = $nbMessages;
    }

    /**
     * @param int|null $nbMessages
     */
    public function increaseNbMessages(?int $nbMessages = 1): void
    {
        $this->nbMessages += $nbMessages;
    }

    /**
     * @return int
     */
    public function getNbLikes(): int
    {
        return $this->nbLikes;
    }

    /**
     * @param int $nbLikes
     */
    public function setNbLikes(int $nbLikes): void
    {
        $this->nbLikes = $nbLikes;
    }

    /**
     * @param int|null $nbLikes can be negative
     */
    public function increaseNbLikes(?int $nbLikes = 1): void
    {
        $this->nbLikes = max($this->nbLikes + $nbLikes, 0);
    }
}