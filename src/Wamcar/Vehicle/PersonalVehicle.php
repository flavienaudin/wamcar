<?php

namespace Wamcar\Vehicle;

use Wamcar\Location\City;
use Wamcar\User\PersonalUser;

class PersonalVehicle extends BaseVehicle
{

    /** @var PersonalUser */
    private $personalUser;

    /**
     * @return PersonalUser
     */
    public function getPersonalUser(): PersonalUser
    {
        return $this->personalUser;
    }

    /**
     * @param PersonalUser $personalUser
     * @return PersonalVehicle
     */
    public function setPersonalUser(PersonalUser $personalUser): PersonalVehicle
    {
        $this->personalUser = $personalUser;
        return $this;
    }


    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->getMake() . ' ' . $this->getModelName();
    }

    /**
     * @return string
     */
    public function getMake(): string
    {
        return $this->modelVersion->getModel()->getMake()->getName();
    }

    /**
     * @return string
     */
    public function getModelName(): string
    {
        return $this->modelVersion->getModel()->getName();
    }

    /**
     * @return string
     */
    public function getModelVersionName(): string
    {
        return $this->modelVersion->getName();
    }

    /**
     * @return string
     */
    public function getEngineName(): string
    {
        return $this->modelVersion->getEngine()->getName();
    }

    /**
     * @return string
     */
    public function getTransmission(): string
    {
        return $this->transmission;
    }

    /**
     * @return string
     */
    public function getFuelName(): string
    {
        return $this->modelVersion->getEngine()->getFuel()->getName();
    }

    /**
     * @return string
     */
    public function getYears(): string
    {
        return $this->getRegistrationDate()->format('Y');
    }

    /**
     * @return \DateTimeInterface
     */
    public function getRegistrationDate(): \DateTimeInterface
    {
        return $this->registrationDate;
    }

    /**
     * @return int
     */
    public function getMileage(): int
    {
        return $this->mileage;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    /**
     * @return string
     */
    public function getCityName(): string
    {
        return $this->city->getName();
    }

    /**
     * @return City
     */
    public function getCity(): City
    {
        return $this->city;
    }
}
