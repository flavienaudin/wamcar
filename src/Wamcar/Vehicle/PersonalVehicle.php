<?php

namespace Wamcar\Vehicle;

use Wamcar\Location\City;
use Wamcar\User\PersonalUser;

class PersonalVehicle extends BaseVehicle
{

    /** @var PersonalUser */
    private $owner;

    /**
     * @return PersonalUser
     */
    public function getOwner(): PersonalUser
    {
        return $this->owner;
    }

    /**
     * @param PersonalUser $owner
     * @return PersonalVehicle
     */
    public function setOwner(PersonalUser $owner): PersonalVehicle
    {
        $this->owner = $owner;
        return $this;
    }

    /**
     * @return City
     */
    private function getCity(): City
    {
        return $this->city;
    }

    /**
     * @return string
     */
    public function getPostalCode(): string
    {
        return $this->getCity()->getPostalCode();
    }

    /**
     * @return string
     */
    public function getCityName(): string
    {
        return $this->getCity()->getName();
    }

    public function getLatitude(): string
    {
        return $this->getCity()->getLatitude();
    }

    public function getLongitude(): string
    {
        return $this->getCity()->getLongitude();
    }
}
