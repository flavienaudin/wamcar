<?php


namespace AppBundle\Form\DTO;


use Wamcar\Garage\Address;
use Wamcar\Location\City;
use Wamcar\Garage\Garage;

class GarageDTO
{
    /** @var bool */
    public $isNew;
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
    /** @var string */
    public $latitude;
    /** @var string */
    public $longitude;

    public function __construct(Garage $garage = null)
    {
        if (null !== $garage) {
            $this->id = $garage->getId();
            $this->name = $garage->getName();
            $this->siren = $garage->getSiren();
            $this->phone = $garage->getPhone();
            $this->email = $garage->getEmail();
            $this->openingHours = $garage->getOpeningHours();
            $this->presentation = $garage->getPresentation();
            $this->benefit = $garage->getBenefit();
            $this->email = $garage->getEmail();
            $this->address = $garage->getAddress()->getAddress();
            $this->postalCode = $garage->getAddress()->getPostalCode();
            $this->cityName = $garage->getAddress()->getCityName();
            $this->latitude = $garage->getAddress()->getLatitude();
            $this->longitude = $garage->getAddress()->getLongitude();
        }
        $this->isNew = $garage === null;
    }

    /**
     * @return null|City
     */
    public function getCity(): ?City
    {
        return ($this->postalCode && $this->cityName) ? new City($this->postalCode, $this->cityName, $this->latitude, $this->longitude) : null;
    }

    /**
     * @return null|Address
     */
    public function getAddress(): ?Address
    {
        $address = null;
        if (null !== $this->address && null !==$this->getCity()) {
            $address = new Address($this->address, $this->getCity());
        }

        return $address;
    }
}
