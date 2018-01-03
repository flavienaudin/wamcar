<?php

namespace Wamcar\User;

use Wamcar\Location\City;

class UserProfile
{
    /** @var  ?Title */
    protected $title;
    /** @var ?string */
    protected $name;
    /** @var ?string */
    protected $phone;
    /** @var  ?City */
    protected $city;

    /**
     * UserProfile constructor.
     * @param Title|null $title
     * @param string|null $name
     * @param string|null $phone
     * @param City|null $city
     */
    public function __construct(
        Title $title = null,
        string $name = null,
        string $phone = null,
        City $city = null
    )
    {
        $this->title = $title;
        $this->name = $name;
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
    public function getName(): ?string
    {
        return $this->name;
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
