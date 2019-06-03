<?php

namespace Wamcar\User;

use Wamcar\Location\City;

class UserProfile
{
    /** @var  ?Title */
    protected $title;
    /** @var string */
    protected $firstName;
    /** @var null|string */
    protected $lastName;
    /** @var string */
    protected $description;
    /** @var null|string */
    protected $phone;
    /** @var bool */
    protected $phoneDisplay;
    /** @var  City|null */
    protected $city;

    /**
     * UserProfile constructor.
     * @param string $firstName
     * @param string|null $lastName
     * @param Title|null $title
     * @param string|null $description
     * @param string|null $phone
     * @param bool|null $phoneDisplay
     * @param City|null $city
     */
    public function __construct(
        string $firstName,
        string $lastName = null,
        Title $title = null,
        string $description = null,
        string $phone = null,
        bool $phoneDisplay = false,
        City $city = null
    )
    {
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->title = $title;
        $this->description = $description;
        $this->phone = $phone;
        $this->phoneDisplay = $phoneDisplay;
        $this->city = $city;
    }

    /**
     * @return Title
     */
    public function getTitle(): ?Title
    {
        return $this->title;
    }


    /**
     * @param mixed $title
     */
    public function setTitle($title): void
    {
        $this->title = $title;
    }


    /**
     * @return string
     */
    public function getFirstName(): string
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     */
    public function setFirstName(string $firstName): void
    {
        $this->firstName = $firstName;
    }

    /**
     * @return string
     */
    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    /**
     * @param mixed $lastName
     */
    public function setLastName($lastName): void
    {
        $this->lastName = $lastName;
    }

    /**
     * @return string
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param null|string $description
     */
    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    /**
     * @return string phone
     */
    public function getPhone(): ?string
    {
        return $this->phone;
    }

    /**
     * @param mixed $phone
     */
    public function setPhone($phone): void
    {
        $this->phone = $phone;
    }

    /**
     * @return bool
     */
    public function isPhoneDisplay(): bool
    {
        return $this->phoneDisplay;
    }

    /**
     * @param bool $phoneDisplay
     */
    public function setPhoneDisplay(bool $phoneDisplay): void
    {
        $this->phoneDisplay = $phoneDisplay;
    }

    /**
     * @return null|City
     */
    public function getCity(): ?City
    {
        return $this->city;
    }

    /**
     * @param mixed $city
     */
    public function setCity($city): void
    {
        $this->city = $city;
    }

    /**
     * @return string|null
     */
    public function getPostalCode(): ?string
    {
        return ($this->getCity() != null ? $this->getCity()->getPostalCode() : null);
    }

    /**
     * @return string|null
     */
    public function getCityName(): ?string
    {
        return ($this->getCity() != null ? $this->getCity()->getName() : null);
    }

    /**
     * @return string|null
     */
    public function getLatitude(): ?string
    {
        return ($this->getCity() != null ? $this->getCity()->getLatitude() : null);
    }

    /**
     * @return string|null
     */
    public function getLongitude(): ?string
    {
        return ($this->getCity() != null ? $this->getCity()->getLongitude() : null);
    }
}
