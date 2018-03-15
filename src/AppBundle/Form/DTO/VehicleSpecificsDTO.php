<?php

namespace AppBundle\Form\DTO;

use Wamcar\Location\City;
use Wamcar\Vehicle\Enum\MaintenanceState;
use Wamcar\Vehicle\Enum\SafetyTestDate;
use Wamcar\Vehicle\Enum\SafetyTestState;

class VehicleSpecificsDTO
{
    /** @var \DateTimeInterface */
    public $registrationDate;
    /** @var int */
    public $mileage;
    /** @var bool */
    public $isTimingBeltChanged;
    /** @var SafetyTestDate */
    public $safetyTestDate;
    /** @var SafetyTestState */
    public $safetyTestState;
    /** @var int|null */
    public $bodyState;
    /** @var int|null */
    public $engineState;
    /** @var int|null */
    public $tyreState;
    /** @var MaintenanceState */
    public $maintenanceState;
    /** @var bool */
    public $isImported;
    /** @var bool */
    public $isFirstHand;
    /** @var string|null */
    public $additionalInformation;
    /** @var string */
    public $postalCode;
    /** @var string */
    public $cityName;
    /** @var string */
    public $latitude;
    /** @var string */
    public $longitude;

    /**
     * VehicleSpecificsDTO constructor.
     */
    public function __construct(?string $date1erCir = null)
    {
        $this->registrationDate = ($date1erCir ? new \DateTimeImmutable($date1erCir) : null);
        $this->safetyTestDate = SafetyTestDate::UNKNOWN();
        $this->safetyTestState = SafetyTestState::UNKNOWN();
        $this->maintenanceState = MaintenanceState::UNKNOWN();
        $this->isTimingBeltChanged = false;
        $this->isImported = false;
        $this->isFirstHand = false;
    }

    /**
     * @return null|City
     */
    public function getCity(): ?City
    {
        return ($this->postalCode && $this->cityName) ? new City($this->postalCode, $this->cityName, $this->latitude, $this->longitude) : null;
    }

    /**
     * @param $registrationDate
     * @param $mileAge
     * @param $isTimingBeltChanged
     * @param $safetyTestDate
     * @param $safetyTestState
     * @param $bodyState
     * @param $engineState
     * @param $tyreState
     * @param $maintenanceState
     * @param $isImported
     * @param $isFirstHand
     * @param $additionalInformation
     * @param $postalCode
     * @param $cityName
     * @param $latitude
     * @param $longitude
     * @return VehicleSpecificsDTO
     */
    public static function buildFromSpecifics(
        $registrationDate,
        $mileAge,
        $isTimingBeltChanged,
        $safetyTestDate,
        $safetyTestState,
        $bodyState,
        $engineState,
        $tyreState,
        $maintenanceState,
        $isImported,
        $isFirstHand,
        $additionalInformation,
        $postalCode,
        $cityName,
        $latitude,
        $longitude
    )
    {
        $dto = new self();
        $dto->registrationDate = $registrationDate;
        $dto->mileage = $mileAge;
        $dto->isTimingBeltChanged = $isTimingBeltChanged;
        $dto->safetyTestDate = $safetyTestDate;
        $dto->safetyTestState = $safetyTestState;
        $dto->bodyState = $bodyState;
        $dto->engineState = $engineState;
        $dto->tyreState = $tyreState;
        $dto->maintenanceState = $maintenanceState;
        $dto->isImported = $isImported;
        $dto->isFirstHand = $isFirstHand;
        $dto->additionalInformation = $additionalInformation;
        $dto->postalCode = $postalCode;
        $dto->cityName = $cityName;
        $dto->latitude = $latitude;
        $dto->longitude = $longitude;

        return $dto;
    }
}
