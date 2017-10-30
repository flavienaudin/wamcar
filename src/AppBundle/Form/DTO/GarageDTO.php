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

    /**
     * GarageDTO constructor.
     * @param ApplicationGarage $garage
     */
    public function __construct(ApplicationGarage $garage = null)
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
            $this->fillFromAddress($garage->getAddress());
        }
    }

    /**
     * @param Address $address
     */
    public function fillFromAddress(Address $address)
    {
        $this->address = $address->getAddress();
        $this->fillFromCity($address->getCity());
    }

    /**
     * @param City $city
     */
    public function fillFromCity(City $city)
    {
        $this->postalCode = $city->getPostalCode();
        $this->cityName = $city->getName();
    }

    /**
     * @return null|City
     */
    public function getCity(): ?City
    {
        $city = null;

        if (!empty($this->postalCode) && !empty($this->cityName))
            $city = new City($this->postalCode, $this->cityName);

        return $city;
    }

    /**
     * @return Address
     */
    public function getAddress(): Address
    {
        $address = new Address($this->address, $this->getCity());

        return $address;
    }
}
