<?php

namespace AppBundle\Form\DTO;

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
}
