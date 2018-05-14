<?php

namespace AppBundle\Form\DTO;

use Wamcar\Location\City;
use Wamcar\Vehicle\Enum\MaintenanceState;
use Wamcar\Vehicle\Enum\SafetyTestDate;
use Wamcar\Vehicle\Enum\SafetyTestState;
use Wamcar\Vehicle\Enum\TimingBeltState;

class VehicleSpecificsDTO
{
    /** @var \DateTimeInterface */
    public $registrationDate;
    /** @var bool */
    public $isUsed;
    /** @var int */
    public $mileage;
    /** @var TimingBeltState|null */
    public $timingBeltState;
    /** @var SafetyTestDate|null */
    public $safetyTestDate;
    /** @var SafetyTestState|null */
    public $safetyTestState;
    /** @var int|null */
    public $bodyState;
    /** @var int|null */
    public $engineState;
    /** @var int|null */
    public $tyreState;
    /** @var MaintenanceState|null */
    public $maintenanceState;
    /** @var bool|null */
    public $isImported;
    /** @var bool|null */
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
     * @var \DateTimeImmutable|null $date1erCir
     * @throws \Exception
     */
    public function __construct(?string $date1erCir = null)
    {
        $this->registrationDate = ($date1erCir ? new \DateTimeImmutable($date1erCir) : null);
        $this->isUsed = false;
        $this->safetyTestDate = null;
        $this->safetyTestState = null;
        $this->maintenanceState = null;
        $this->timingBeltState = null;
        $this->isImported = null;
        $this->isFirstHand = null;
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
     * @param $isUsed
     * @param $mileAge
     * @param $timingBeltState
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
        $isUsed,
        $mileAge,
        $timingBeltState,
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
        $dto->isUsed = $isUsed;
        $dto->mileage = $mileAge;
        $dto->timingBeltState = $timingBeltState;
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
