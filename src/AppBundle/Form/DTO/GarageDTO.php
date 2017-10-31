<?php


namespace AppBundle\Form\DTO;


use AppBundle\Doctrine\Entity\ApplicationGarage;
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

    public function __construct(ApplicationGarage $applicationGarage = null)
    {
        if (null !== $applicationGarage) {
            $this->id = $applicationGarage->getId();
            $this->name = $applicationGarage->getName();
            $this->siren = $applicationGarage->getSiren();
            $this->phone = $applicationGarage->getPhone();
            $this->email = $applicationGarage->getEmail();
            $this->openingHours = $applicationGarage->getOpeningHours();
            $this->presentation = $applicationGarage->getPresentation();
            $this->benefit = $applicationGarage->getBenefit();
            $this->email = $applicationGarage->getEmail();
            $this->address = $applicationGarage->getAddress()->getAddress();
            $this->postalCode = $applicationGarage->getAddress()->getCity()->getPostalCode();
            $this->cityName = $applicationGarage->getAddress()->getCity()->getName();
        }
    }

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
