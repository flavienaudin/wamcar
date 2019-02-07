<?php

namespace Wamcar\Garage;

use Wamcar\Location\City;

class Address
{
    /** @var string|null */
    private $address;
    /** @var  City */
    private $city;

    /**
     * Address constructor.
     * @param string|null $address
     * @param City $city
     */
    public function __construct(?string $address, City $city)
    {
        $this->address = $address;
        $this->city = $city;
    }

    /**
     * @return string|null
     */
    public function getAddress(): ?string
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
     * @return City
     */
    public function getCity(): City
    {
        return $this->city;
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
        return $this->address ?? '';
    }

    /**
     * To display the full address
     * @return string
     */
    public function getFullAddress(): string
    {
        return $this->__toString() . ' ' .
            ($this->getCity() ? $this->getPostalCode() : '') . ' ' .
            ($this->getCity() ? $this->getCityName() : '');
    }
}
