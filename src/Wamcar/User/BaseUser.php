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

    /**
     * User constructor.
     * @param string $email
     */
    public function __construct(
        string $email
    )
    {
        $this->email = $email;
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
     * @param BaseUser|null $user null if user not connected
     * @return bool
     */
    public function canSeeMyVehicles(BaseUser $user = null): bool
    {
        return $this->getType() == ProUser::TYPE ||
            $this->is($user) ||
            ($user != null && $user->getType() == ProUser::TYPE);
    }
}
