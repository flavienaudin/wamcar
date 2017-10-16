<?php

namespace Wamcar\User;

use Wamcar\Vehicle\Vehicle;

class User
{
    /** @var int */
    protected $id;
    /** @var string */
    protected $email;
    /** @var  ?Title */
    protected $title;
    /** @var ?string */
    protected $name;
    /** @var ?string */
    protected $phone;
    /** @var  ?City */
    protected $city;

    /** @var Vehicle[]|array */
    protected $vehicles;

    /**
     * User constructor.
     * @param string $email
     * @param Vehicle|null $firstVehicle
     */
    public function __construct(
        string $email,
        Vehicle $firstVehicle = null
    )
    {
        $this->email = $email;
        $this->vehicles = $firstVehicle ? [$firstVehicle] : [];
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @return Title|null
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return string|null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string|null
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @return City|null
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @return array|Vehicle[]
     */
    public function getVehicles()
    {
        return $this->vehicles;
    }
}
