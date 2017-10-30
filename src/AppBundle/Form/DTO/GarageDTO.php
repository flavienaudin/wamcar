<?php


namespace AppBundle\Form\DTO;


use Wamcar\Garage\Address;
use Wamcar\Garage\City;

class GarageDTO
{
    /** @var int */
    public $id;
    /** @var string */
    public $name;
    /** @var  string */
    public $siren;
    /** @var  string */
    public $phone;
    /** @var  string */
    public $email;
    /** @var  string */
    public $openingHours;
    /** @var  string */
    public $presentation;
    /** @var  string */
    public $benefit;
    /** @var string */
    public $address;
    /** @var string */
    public $postalCode;
    /** @var string */
    public $cityName;

    /**
     * @return City
     */
    public function fillCity(): City
    {
        $city = new City($this->postalCode, $this->cityName);

        return $city;
    }

    /**
     * @return Address
     */
    public function fillAddress(): Address
    {
        $address = new Address($this->address, $this->fillCity());

        return $address;
    }

}
