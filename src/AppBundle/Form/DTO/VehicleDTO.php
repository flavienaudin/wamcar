<?php

namespace AppBundle\Form\DTO;

use Wamcar\Location\City;
use Wamcar\Vehicle\Engine;
use Wamcar\Vehicle\Enum\MaintenanceState;
use Wamcar\Vehicle\Enum\SafetyTestDate;
use Wamcar\Vehicle\Enum\SafetyTestState;
use Wamcar\Vehicle\Enum\TimingBeltState;
use Wamcar\Vehicle\Enum\Transmission;
use Wamcar\Vehicle\Fuel;
use Wamcar\Vehicle\Make;
use Wamcar\Vehicle\Model;
use Wamcar\Vehicle\ModelVersion;
use Wamcar\Vehicle\Registration;

class VehicleDTO
{
    const DEFAULT_PICTURE_COUNT = 4;

    /** @var string */
    public $id;
    /** @var VehicleRegistrationDTO */
    public $vehicleRegistration;
    /** @var VehicleInformationDTO */
    public $information;
    /** @var VehicleSpecificsDTO */
    public $specifics;
    /** @var VehiclePictureDTO[]|array */
    public $pictures;
    /** @var bool */
    public $vehicleReplace;

    /**
     * VehicleDTO constructor.
     * @param string|null $registrationNumber
     * @param string|null $date1erCir
     * @param null $vin
     */
    public function __construct(string $registrationNumber = null, string $date1erCir = null, $vin = null)
    {
        $this->vehicleRegistration = new VehicleRegistrationDTO(null, $registrationNumber, $vin);
        $this->information = new VehicleInformationDTO();
        $this->specifics = new VehicleSpecificsDTO($date1erCir);
        $this->pictures = self::initFormPictureVehicle([]);
    }

    /**
     * @param array $filters
     */
    public function updateFromFilters(array $filters = []): void
    {
        $this->information->updateFromFilters($filters);
    }

    /**
     * @param array $pictures
     * @return array
     */
    public static function initFormPictureVehicle(array $pictures = []): array
    {
        if (count($pictures) < self::DEFAULT_PICTURE_COUNT) {
            for ($i = count($pictures); $i < self::DEFAULT_PICTURE_COUNT; $i++) {
                $pictures[] = new VehiclePictureDTO();
            }
        } else {
            $pictures[] = new VehiclePictureDTO();
        }

        return $pictures;
    }

    /**
     * @return array
     */
    public function retrieveFilter(): array
    {
        return $this->information->retrieveFilter();
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
     * @return VehicleRegistrationDTO
     */
    public function getVehicleRegistration(): VehicleRegistrationDTO
    {
        return $this->vehicleRegistration;
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
     * @return bool
     */
    public function isUsed(): bool
    {
        return $this->specifics->isUsed;
    }

    /**
     * @return int
     */
    public function getMileage(): int
    {
        return $this->specifics->mileage;
    }

    /**
     * @return TimingBeltState|null
     */
    public function getTimingBeltState(): ?TimingBeltState
    {
        return $this->specifics->timingBeltState;
    }

    /**
     * @return SafetyTestDate|null
     */
    public function getSafetyTestDate(): ?SafetyTestDate
    {
        return $this->specifics->safetyTestDate;
    }

    /**
     * @return SafetyTestState|null
     */
    public function getSafetyTestState(): ?SafetyTestState
    {
        return $this->specifics->safetyTestState;
    }

    /**
     * @return int
     */
    public function getBodyState(): ?int
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
     * @return MaintenanceState|null
     */
    public function getMaintenanceState(): ?MaintenanceState
    {
        return $this->specifics->maintenanceState;
    }

    /**
     * @return bool|null
     */
    public function isImported(): ?bool
    {
        return $this->specifics->isImported;
    }

    /**
     * @return bool|null
     */
    public function isFirstHand(): ?bool
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
     * @return City
     */
    public function getCity(): City
    {
        return $this->specifics->getCity();
    }

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

    /**
     * @param \DateTimeImmutable|string $registrationDate
     */
    public function setRegistrationDate($registrationDate = null): self
    {
        if (!$registrationDate instanceof \DateTimeInterface) {
            $registrationDate = new \DateTimeImmutable($registrationDate);
        }
        $this->specifics->registrationDate = $registrationDate;

        return $this;
    }

    public function setRegistrationVin($vin = null): self
    {
        if ($this->vehicleRegistration) {
            $this->vehicleRegistration->setVin($vin);
        } else {
            $this->vehicleRegistration = new Registration(null, null, $vin);
        }
        return $this;
    }

    /**
     * @param City|null $city
     */
    public function setCity(?City $city)
    {
        if ($city != null) {
            $this->specifics->postalCode = $city->getPostalCode();
            $this->specifics->cityName = $city->getName();
            $this->specifics->longitude = $city->getLongitude();
            $this->specifics->latitude = $city->getLatitude();
        }
    }


}
