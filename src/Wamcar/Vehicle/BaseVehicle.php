<?php

namespace Wamcar\Vehicle;

use Wamcar\Vehicle\Enum\MaintenanceState;
use Wamcar\Vehicle\Enum\SafetyTestState;
use Wamcar\Vehicle\Enum\Transmission;

abstract class BaseVehicle implements Vehicle
{
    /** @var ModelVersion */
    private $modelVersion;
    /** @var Transmission */
    private $transmission;
    /** @var Registration|null */
    private $registration;
    /** @var \DateTimeImmutable */
    private $registrationDate;
    /** @var Picture[]|array */
    private $pictures;
    /** @var SafetyTestState */
    private $safetyTest;
    /** @var int */
    private $bodyState;
    /** @var int|null */
    private $engineState;
    /** @var int|null */
    private $tyreState;
    /** @var MaintenanceState */
    private $maintenanceState;
    /** @var bool|null */
    private $isTimingBeltChanged;
    /** @var bool|null */
    private $isImported;
    /** @var bool|null */
    private $isFirstHand;
    /** @var string|null */
    private $additionalInformation;

    /**
     * BaseVehicle constructor.
     * @param ModelVersion $modelVersion
     * @param Transmission $transmission
     * @param Registration|null $registration
     * @param \DateTimeImmutable $registrationDate
     * @param array $pictures
     * @param SafetyTestState $safetyTest
     * @param int $bodyState
     * @param int|null $engineState
     * @param int|null $tyreState
     * @param MaintenanceState $maintenanceState
     * @param bool|null $isTimingBeltChanged
     * @param bool|null $isImported
     * @param bool|null $isFirstHand
     * @param string|null $additionalInformation
     */
    public function __construct(
        ModelVersion $modelVersion,
        Transmission $transmission,
        Registration $registration = null,
        \DateTimeImmutable $registrationDate,
        array $pictures,
        SafetyTestState $safetyTest,
        int $bodyState,
        int $engineState = null,
        int $tyreState = null,
        MaintenanceState $maintenanceState,
        bool $isTimingBeltChanged = null,
        bool $isImported = null,
        bool $isFirstHand = null,
        string $additionalInformation = null)
    {
        $this->modelVersion = $modelVersion;
        $this->transmission = $transmission;
        $this->registration = $registration;
        $this->registrationDate = $registrationDate;
        $this->pictures = $pictures;
        $this->safetyTest = $safetyTest;
        $this->bodyState = $bodyState;
        $this->engineState = $engineState;
        $this->tyreState = $tyreState;
        $this->maintenanceState = $maintenanceState;
        $this->isTimingBeltChanged = $isTimingBeltChanged;
        $this->isImported = $isImported;
        $this->isFirstHand = $isFirstHand;
        $this->additionalInformation = $additionalInformation;
    }


}
