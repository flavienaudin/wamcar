<?php


namespace AppBundle\Form\DTO;


use Wamcar\Garage\Address;
use Wamcar\Garage\Garage;
use Wamcar\Location\City;

class GarageDTO
{
    /** @var bool */
    public $isNew;
    /** @var int */
    public $id;
    /** @var string */
    public $googlePlaceId;
    /** @var string */
    public $name;
    /** @var string */
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
    /** @var  float */
    public $googleRating;
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
    /** @var GaragePictureDTO */
    public $banner;
    /** @var GaragePictureDTO */
    public $logo;

    public function __construct(Garage $garage = null)
    {
        if (null !== $garage) {
            $this->id = $garage->getId();
            $this->googlePlaceId = $garage->getGooglePlaceId();
            $this->name = $garage->getName();
            $this->siren = $garage->getSiren();
            $this->phone = $garage->getPhone();
            $this->email = $garage->getEmail();
            $this->openingHours = $garage->getOpeningHours();
            $this->presentation = $garage->getPresentation();
            $this->benefit = $garage->getBenefit();
            $this->googleRating = $garage->getGoogleRating();
            $this->email = $garage->getEmail();
            $this->address = $garage->getAddress()->getAddress();
            $this->postalCode = $garage->getAddress()->getPostalCode();
            $this->cityName = $garage->getAddress()->getCityName();
            $this->latitude = $garage->getAddress()->getLatitude();
            $this->longitude = $garage->getAddress()->getLongitude();
            if ($garage->getBanner()) {
                $this->banner = new GaragePictureDTO($garage->getBannerFile());
            }
            if ($garage->getLogo()) {
                $this->logo = new GaragePictureDTO($garage->getLogoFile());
            }
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
        if (null !== $this->getCity()) {
            $address = new Address($this->address, $this->getCity());
        }

        return $address;
    }

    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getSiren(): ?string
    {
        return $this->siren;
    }

}
