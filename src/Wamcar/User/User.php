<?php

namespace Wamcar\User;

use Wamcar\Vehicle\Vehicle;

class User
{
    /** @var string */
    private $email;
    /** @var ?string */
    private $civility;
    /** @var ?string */
    private $name;
    /** @var ?string */
    private $phone;
    /** @var ?string */
    private $zipCode;
    /** @var ?string */
    private $city;
    /** @var Vehicle[]|array */
    private $vehicles;

    /**
     * User constructor.
     * @param string $email
     * @param string|null $name
     * @param string|null $civility
     * @param string|null $phone
     * @param string|null $zipCode
     * @param string|null $city
     * @param Vehicle|null $firstVehicle
     */
    public function __construct(
        string $email,
        string $name = null,
        string $civility = null,
        string $phone = null,
        string $zipCode = null,
        string $city = null,
        Vehicle $firstVehicle = null)
    {
        $this->email = $email;
        $this->civility = $civility;
        $this->name = $name;
        $this->phone = $phone;
        $this->zipCode = $zipCode;
        $this->city = $city;
        $this->vehicles = $firstVehicle ? [$firstVehicle] : [];
    }


}
