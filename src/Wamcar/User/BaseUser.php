<?php

namespace Wamcar\User;

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

    /**
     * User constructor.
     * @param string $email
     * @param Picture|null $avatar
     */
    public function __construct(
        string $email,
        Picture $avatar = null
    )
    {
        $this->email = $email;
        $this->avatar = $avatar;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }


    public function getName(): ?string
    {
        return (null !== $this->getUserProfile() ? $this->getUserProfile()->getName() : null);
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
}
