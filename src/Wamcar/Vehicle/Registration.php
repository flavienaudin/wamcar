<?php

namespace Wamcar\Vehicle;

use AppBundle\Form\DTO\VehicleRegistrationDTO;

/**
 * Car registration document
 *
 * Class Registration
 * @package Wamcar\Vehicle
 */
final class Registration
{
    /** @var string|null */
    private $mineType;
    /** @var string|null */
    private $plateNumber;
    /** @var string|null */
    private $vin;

    /**
     * Registration constructor.
     */
    public function __construct(string $mineType = null, string $plateNumber = null, string $vin = null)
    {
        $this->mineType = $mineType;
        $this->plateNumber = $plateNumber;
        $this->vin = $vin;
    }

    public static function createFromVehicleRegistrationDTO(VehicleRegistrationDTO $vehicleRegistrationDTO = null)
    {
        if ($vehicleRegistrationDTO) {
            return new self($vehicleRegistrationDTO->getMineType(), $vehicleRegistrationDTO->getPlateNumber(), $vehicleRegistrationDTO->getVin());
        }
        return null;
    }

    /**
     * @return string|null
     */
    public function getMineType(): ?string
    {
        return $this->mineType;
    }

    /**
     * @return string|null
     */
    public function getPlateNumber(): ?string
    {
        return $this->plateNumber;
    }

    /**
     * @return string|null
     */
    public function getVin(): ?string
    {
        return $this->vin;
    }
}
