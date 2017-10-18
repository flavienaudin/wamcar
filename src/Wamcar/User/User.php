<?php

namespace Wamcar\User;

use Wamcar\Vehicle\Vehicle;

class User
{
    /** @var int */
    protected $id;
    /** @var string */
    protected $email;
    /** @var  ?UserProfile */
    protected $userProfile;

    /** @var Vehicle[]|array */
    protected $vehicles;

    /**
     * User constructor.
     * @param string $email
     * @param UserProfile|null $userProfile
     * @param Vehicle|null $firstVehicle
     */
    public function __construct(
        string $email,
        UserProfile $userProfile = null,
        Vehicle $firstVehicle = null
    )
    {
        $this->email = $email;
        $this->userProfile = $userProfile;
        $this->vehicles = $firstVehicle ? [$firstVehicle] : [];
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
     * @return array|Vehicle[]
     */
    public function getVehicles()
    {
        return $this->vehicles;
    }
}
