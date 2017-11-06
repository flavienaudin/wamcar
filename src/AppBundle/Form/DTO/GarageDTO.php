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
        dump($applicationGarage);
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
            $this->postalCode = $applicationGarage->getAddress()->getPostalCode();
            $this->cityName = $applicationGarage->getAddress()->getCityName();
        }
    }

    /**
     * @return null|City
     */
    public function getCity(): ?City
    {
        $city = null;
        if (null !== $this->postalCode && null !==$this->cityName) {
            $city = new City($this->postalCode, $this->cityName);
        }

        return $city;
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
