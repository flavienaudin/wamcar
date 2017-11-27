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
    /** @var int */
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
    public function __construct()
    {
        $this->registrationDate = new \DateTimeImmutable('last year');
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
}
