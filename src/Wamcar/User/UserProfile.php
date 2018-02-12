<?php

namespace Wamcar\User;

use Wamcar\Location\City;

class UserProfile
{
    /** @var  ?Title */
    protected $title;
    /** @var string */
    protected $firstName;
    /** @var ?string */
    protected $lastName;
    /** @var string */
    protected $description;
    /** @var ?string */
    protected $phone;
    /** @var  ?City */
    protected $city;

    /**
     * UserProfile constructor.
     * @param Title|null $title
     * @param string $firstName
     * @param string|null $lastName
     * @param string|null $description
     * @param string|null $phone
     * @param City|null $city
     */
    public function __construct(
        Title $title = null,
        string $firstName,
        string $lastName = null,
        string $description = null,
        string $phone = null,
        City $city = null
    )
    {
        $this->title = $title;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->description = $description;
        $this->phone = $phone;
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
     * @return string
     */
    public function getFirstName(): string
    {
        return $this->firstName;
    }

    /**
     * @return string
     */
    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    /**
     * @return string
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @return string phone
     */
    public function getPhone(): ?string
    {
        return $this->phone;
    }

    /**
     * @return null|City
     */
    public function getCity(): ?City
    {
        return $this->city;
    }
}
