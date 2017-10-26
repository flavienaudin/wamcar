<?php

namespace Wamcar\Garage;

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
     * @return City
     */
    public function getCity(): City
    {
        return $this->city;
    }

}
