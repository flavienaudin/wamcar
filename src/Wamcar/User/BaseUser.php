<?php

namespace Wamcar\User;

use AppBundle\Doctrine\Entity\UserPicture;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\HttpFoundation\File\File;
use Wamcar\Conversation\ConversationUser;
use Wamcar\Conversation\Message;

abstract class BaseUser
{
    const TYPE = '';

    /** @var int */
    protected $id;
    /** @var string */
    protected $email;
    /** @var  ?UserProfile */
    protected $userProfile;
    /** @var ?Picture */
    protected $avatar;
    /** @var  Collection <Message> */
    protected $messages;
    /** @var  Collection <ConversationUser> */
    protected $conversationUsers;

    /**
     * User constructor.
     * @param string $email
     * @param string $firstName
     * @param ?string|null $name
     * @param Picture|null $avatar
     */
    public function __construct(
        string $email,
        string $firstName,
        string $name = null,
        Picture $avatar = null
    )
    {
        $this->email = $email;
        $this->userProfile = new UserProfile(null, $firstName, $name);
        $this->avatar = $avatar;
        $this->messages = new ArrayCollection();
        $this->conversationUsers = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getFullName(): ?string
    {
        return $this->getFirstName().($this->getLastName()?' '.$this->getLastName():'');
    }

    /**
     * @return string
     */
    public function getFirstName(): string
    {
        return $this->getUserProfile()->getFirstName();
    }

    /**
     * @return string
     */
    public function getLastName(): ?string
    {
        return $this->getUserProfile()->getLastName();
    }

    /**
     * @return string
     */
    public function getDescription(): ?string
    {
        return (null !== $this->getUserProfile() ? $this->getUserProfile()->getDescription() : null);
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail(string $email)
    {
        $this->email = $email;
    }

    /**
     * @return UserProfile
     */
    public function getUserProfile(): ?UserProfile
    {
        return $this->userProfile;
    }

    /**
     * @param null|UserProfile $userProfile
     */
    public function updateUserProfile($userProfile)
    {
        $this->userProfile = $userProfile;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return static::TYPE;
    }

    /**
     * @param mixed|null $user
     * @return bool
     */
    public function is($user): bool
    {
        return $user instanceof self && $user->getId() === $this->getId();
    }

    /**
     * @return null|Picture
     */
    public function getAvatar(): ?Picture
    {
        return $this->avatar;
    }

    /**
     * @return null|File
     */
    public function getAvatarFile(): ?File
    {
        return $this->avatar ? $this->avatar->getFile() : null;
    }

    /**
     * @return Collection <Message>
     */
    public function getMessages(): Collection
    {
        return $this->messages;
    }

    /**
     * @return Collection <ConversationUser>
     */
    public function getConversationUsers(): Collection
    {
        return $this->conversationUsers;
    }

    /**
     * @param UserPicture $avatar
     */
    public function setAvatar(?UserPicture $avatar)
    {
        $this->avatar = $avatar;
    }

    /**
     * @return bool
     */
    public function isPro(): bool
    {
        return $this->getType() === ProUser::TYPE;
    }

    /**
     * @return bool
     */
    public function isPersonal(): bool
    {
        return $this->getType() === PersonalUser::TYPE;
    }

    /**
     * @param BaseUser|null $user null if user not connected
     * @return bool
     */
    abstract public function canSeeMyVehicles(BaseUser $user = null): bool;

    /**
     * @return Collection
     */
    abstract public function getVehicles(): Collection;
}
