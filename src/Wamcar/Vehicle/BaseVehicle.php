<?php

namespace Wamcar\Vehicle;

use Ramsey\Uuid\Uuid;
use Wamcar\Vehicle\Enum\MaintenanceState;
use Wamcar\Vehicle\Enum\SafetyTestState;
use Wamcar\Vehicle\Enum\Transmission;

abstract class BaseVehicle implements Vehicle
{
    /** @var string */
    protected $id;
    /** @var ModelVersion */
    protected $modelVersion;
    /** @var Transmission */
    protected $transmission;
    /** @var Registration|null */
    protected $registration;
    /** @var \DateTimeImmutable */
    protected $registrationDate;
    /** @var Picture[]|array */
    protected $pictures;
    /** @var SafetyTestState */
    protected $safetyTest;
    /** @var int */
    protected $bodyState;
    /** @var int|null */
    protected $engineState;
    /** @var int|null */
    protected $tyreState;
    /** @var MaintenanceState */
    protected $maintenanceState;
    /** @var bool|null */
    protected $isTimingBeltChanged;
    /** @var bool|null */
    protected $isImported;
    /** @var bool|null */
    protected $isFirstHand;
    /** @var string|null */
    protected $additionalInformation;

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
        $this->id = Uuid::uuid4();
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

    /**
     * @param Picture $picture
     */
    public function addPicture(Picture $picture): void
    {
        $this->pictures[] = $picture;
    }
}
