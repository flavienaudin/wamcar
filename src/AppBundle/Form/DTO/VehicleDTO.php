<?php

namespace AppBundle\Form\DTO;

use Wamcar\Vehicle\Enum\MaintenanceState;
use Wamcar\Vehicle\Enum\SafetyTestDate;
use Wamcar\Vehicle\Enum\SafetyTestState;
use Wamcar\Vehicle\Enum\Transmission;
use Wamcar\Vehicle\ModelVersion;

class VehicleDTO
{
    const DEFAULT_PICTURE_COUNT = 4;

    /** @var string */
    public $registrationNumber;
    /** @var VehicleIdentificationDTO */
    public $identification;
    /** @var VehicleSpecificsDTO */
    public $specifics;
    /** @var VehiclePictureDTO[]|array */
    public $pictures;

    /**
     * VehicleDTO constructor.
     */
    public function __construct()
    {
        $this->pictures = array_map(function () {
            return new VehiclePictureDTO();
        }, range(1, self::DEFAULT_PICTURE_COUNT));
        $this->identification = new VehicleIdentificationDTO();
        $this->specifics = new VehicleSpecificsDTO();
    }

    /**
     * @param VehiclePictureDTO $picture
     */
    public function addPicture(VehiclePictureDTO $picture): void
    {
        $this->pictures[] = $picture;
    }

    /**
     * @param VehiclePictureDTO $picture
     */
    public function removePicture(VehiclePictureDTO $picture): void
    {
        if (($key = array_search($picture, $this->pictures, true)) !== FALSE) {
            unset($this->pictures[$key]);
        }
    }

    /**
     * @return ModelVersion
     */
    public function getModelVersion(): ?ModelVersion
    {
        return $this->identification->getModelVersion();
    }

    /**
     * @return Transmission
     */
    public function getTransmission(): Transmission
    {
        return $this->identification->transmission;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getRegistrationDate(): \DateTimeInterface
    {
        return $this->specifics->registrationDate;
    }

    /**
     * @return int
     */
    public function getMileage(): int
    {
        return $this->specifics->mileage;
    }

    /**
     * @return bool
     */
    public function isTimingBeltChanged(): bool
    {
        return $this->specifics->isTimingBeltChanged;
    }

    /**
     * @return SafetyTestDate
     */
    public function getSafetyTestDate(): SafetyTestDate
    {
        return $this->specifics->safetyTestDate;
    }

    /**
     * @return SafetyTestState
     */
    public function getSafetyTestState(): SafetyTestState
    {
        return $this->specifics->safetyTestState;
    }

    /**
     * @return int
     */
    public function getBodyState(): int
    {
        return $this->specifics->bodyState;
    }

    /**
     * @return int|null
     */
    public function getEngineState(): ?int
    {
        return $this->specifics->engineState;
    }

    /**
     * @return int|null
     */
    public function getTyreState(): ?int
    {
        return $this->specifics->tyreState;
    }

    /**
     * @return MaintenanceState
     */
    public function getMaintenanceState(): MaintenanceState
    {
        return $this->specifics->maintenanceState;
    }

    /**
     * @return bool
     */
    public function isImported(): bool
    {
        return $this->specifics->isImported;
    }

    /**
     * @return bool
     */
    public function isFirstHand(): bool
    {
        return $this->specifics->isFirstHand;
    }

    /**
     * @return string
     */
    public function getAdditionalInformation(): ?string
    {
        return $this->specifics->additionalInformation;
    }

}
