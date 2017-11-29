<?php

namespace Wamcar\Vehicle;

class PersonalVehicle extends BaseVehicle
{
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
}
