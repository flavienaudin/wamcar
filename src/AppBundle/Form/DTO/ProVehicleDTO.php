<?php

namespace AppBundle\Form\DTO;

use Wamcar\Vehicle\Engine;
use Wamcar\Vehicle\Enum\MaintenanceState;
use Wamcar\Vehicle\Enum\SafetyTestDate;
use Wamcar\Vehicle\Enum\SafetyTestState;
use Wamcar\Vehicle\Enum\Transmission;
use Wamcar\Vehicle\Fuel;
use Wamcar\Vehicle\Make;
use Wamcar\Vehicle\Model;
use Wamcar\Vehicle\ModelVersion;

class ProVehicleDTO
{
    const DEFAULT_PICTURE_COUNT = 4;

    /** @var string */
    public $registrationNumber;
    /** @var VehicleInformationDTO */
    public $information;
    /** @var VehicleSpecificsDTO */
    public $specifics;
    /** @var VehiclePictureDTO[]|array */
    public $pictures;
    /** @var VehicleOfferDTO */
    public $offer;

    /**
     * VehicleDTO constructor.
     */
    public function __construct(string $registrationNumber = null)
    {
        $this->pictures = array_map(function () {
            return new VehiclePictureDTO();
        }, range(1, self::DEFAULT_PICTURE_COUNT));

        $this->registrationNumber = $registrationNumber;
        $this->information = new VehicleInformationDTO();
        $this->specifics = new VehicleSpecificsDTO();
        $this->offer = new VehicleOfferDTO();
    }

    /**
     * @param array $filters
     */
    public function updateFromFilters(array $filters = []): void
    {
        $this->information->updateFromFilters($filters);
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

    public function getMake(): ?Make
    {
        return $this->getModel() ? $this->getModel()->getMake() : null;
    }

    public function getModel(): ?Model
    {
        return $this->getModelVersion() ? $this->getModelVersion()->getModel() : null;
    }

    public function getEngine(): ?Engine
    {
        return $this->getModelVersion() ? $this->getModelVersion()->getEngine() : null;
    }

    public function getFuel(): ?Fuel
    {
        return $this->getEngine() ? $this->getEngine()->getFuel() : null;
    }

    /**
     * @return ModelVersion
     */
    public function getModelVersion(): ?ModelVersion
    {
        return $this->information->getModelVersion();
    }

    /**
     * @return Transmission
     */
    public function getTransmission(): Transmission
    {
        return $this->information->transmission ?? Transmission::MANUAL();
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

    /**
     * @return float
     */
    public function getPrice(): ?float
    {
        return $this->offer->price;
    }

    /**
     * @return null|float
     */
    public function getCatalogPrice(): ?float
    {
        return $this->offer->catalogPrice;
    }

    /**
     * @return null|float
     */
    public function getDiscount(): ?float
    {
        return $this->offer->discount;
    }

    /**
     * @return string
     */
    public function getGuarantee(): ?string
    {
        return $this->offer->guarantee;
    }

    /**
     * @return bool
     */
    public function isRefunded(): bool
    {
        return $this->offer->refunded;
    }

    /**
     * @return string
     */
    public function getOtherGuarantee(): string
    {
        return $this->offer->otherGuarantee;
    }

    /**
     * @return string
     */
    public function getAdditionalServices(): string
    {
        return $this->offer->additionalServices;
    }

    /**
     * @return string
     */
    public function getReferences(): string
    {
        return $this->offer->references;
    }

//
//    /**
//     * @return VehicleInformationDTO
//     */
//    public function getInformation(): VehicleInformationDTO
//    {
//        return $this->information;
//    }
//
//    /**
//     * @param VehicleInformationDTO $information
//     */
//    public function setInformation(VehicleInformationDTO $information): void
//    {
//        $this->information = $information;
//    }
    /**
     * @param Transmission $transmission
     */
    public function setTransmission(Transmission $transmission): void
    {
        $this->information->transmission = $transmission;
    }
    public function setMake($make): void
    {
        $this->information->make = new Make($make);
    }
    public function setFuel($fuel): void
    {
        $this->information->fuel = new Fuel($fuel);
    }

}
