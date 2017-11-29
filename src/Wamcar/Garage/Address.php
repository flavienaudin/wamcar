<?php

namespace Wamcar\Garage;

use Wamcar\Location\City;

class Address
{
    /** @var string */
    private $address;
    /** @var  City */
    private $city;

    /**
     * Address constructor.
     * @param string $address
     * @param City $city
     */
    public function __construct(string $address, City $city)
    {
        $this->address = $address;
        $this->city = $city;
    }

    /**
     * @return string
     */
    public function getAddress(): string
    {
        return $this->address;
    }

    /**
     * @return string
     */
    public function getPostalCode(): string
    {
        return $this->city->getPostalCode();
    }

    /**
     * @return string
     */
    public function getCityName(): string
    {
        return $this->city->getName();
    }

    /**
     * @return string
     */
    public function getLatitude(): ?string
    {
        return $this->city->getLatitude();
    }

    /**
     * @return string
     */
    public function getLongitude(): ?string
    {
        return $this->city->getLongitude();
    }

    public function __toString()
    {
        return $this->address;
    }
}
