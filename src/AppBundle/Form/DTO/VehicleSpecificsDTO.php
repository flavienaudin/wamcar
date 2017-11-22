<?php

namespace AppBundle\Form\DTO;

use Wamcar\Location\City;
use Wamcar\Vehicle\Enum\MaintenanceState;
use Wamcar\Vehicle\Enum\SafetyTestDate;
use Wamcar\Vehicle\Enum\SafetyTestState;
use Wamcar\Vehicle\ProVehicle;

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
    public function __construct(ProVehicle $vehicle = null)
    {
        if ($vehicle) {
            $this->registrationDate = $vehicle->getRegistrationDate();
            $this->mileage = $vehicle->getMileage();
            $this->safetyTestDate = $vehicle->getSafetyTestDate();
            $this->safetyTestState = $vehicle->getSafetyTestState();
            $this->maintenanceState = $vehicle->getMaintenanceState();
            $this->isTimingBeltChanged = $vehicle->getisTimingBeltChanged();
            $this->isImported = $vehicle->getisImported();
            $this->isFirstHand = $vehicle->getisFirstHand();
            $this->bodyState = $vehicle->getBodyState();
            $this->engineState = $vehicle->getEngineState();
            $this->tyreState = $vehicle->getTyreState();
            $this->additionalInformation = $vehicle->getAdditionalInformation();
            $this->postalCode = $vehicle->getPostalCode();
            $this->cityName = $vehicle->getCityName();
        } else {
            $this->registrationDate = new \DateTimeImmutable('last year');
            $this->safetyTestDate = SafetyTestDate::UNKNOWN();
            $this->safetyTestState = SafetyTestState::UNKNOWN();
            $this->maintenanceState = MaintenanceState::UNKNOWN();
            $this->isTimingBeltChanged = false;
            $this->isImported = false;
            $this->isFirstHand = false;
        }
    }

    /**
     * @return null|City
     */
    public function getCity(): ?City
    {
        return ($this->postalCode && $this->cityName) ? new City($this->postalCode, $this->cityName, $this->latitude, $this->longitude) : null;
    }
}
