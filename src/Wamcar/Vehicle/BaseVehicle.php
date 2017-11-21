<?php

namespace Wamcar\Vehicle;

use Ramsey\Uuid\Uuid;
use Wamcar\Location\City;
use Wamcar\Vehicle\Enum\{
    MaintenanceState, SafetyTestDate, SafetyTestState, Transmission
};

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
    /** @var \DateTimeInterface */
    protected $registrationDate;
    /** @var int */
    protected $mileage;
    /** @var Picture[]|array */
    protected $pictures;
    /** @var SafetyTestDate */
    protected $safetyTestDate;
    /** @var SafetyTestState */
    protected $safetyTestState;
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
    /** @var City */
    protected $city;
    /** @var \DateTimeInterface */
    protected $createdAt;

    /**
     * BaseVehicle constructor.
     * @param ModelVersion $modelVersion
     * @param Transmission $transmission
     * @param Registration|null $registration
     * @param \DateTimeInterface $registrationDate
     * @param int $mileage
     * @param array $pictures
     * @param SafetyTestDate $safetyTestDate
     * @param SafetyTestState $safetyTestState
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
        \DateTimeInterface $registrationDate,
        int $mileage,
        array $pictures,
        SafetyTestDate $safetyTestDate,
        SafetyTestState $safetyTestState,
        int $bodyState,
        int $engineState = null,
        int $tyreState = null,
        MaintenanceState $maintenanceState,
        bool $isTimingBeltChanged = null,
        bool $isImported = null,
        bool $isFirstHand = null,
        string $additionalInformation = null,
        City $city = null)
    {
        $this->id = Uuid::uuid4();
        $this->modelVersion = $modelVersion;
        $this->transmission = $transmission;
        $this->registration = $registration;
        $this->registrationDate = $registrationDate;
        $this->mileage = $mileage;
        $this->pictures = $pictures;
        $this->safetyTestDate = $safetyTestDate;
        $this->safetyTestState = $safetyTestState;
        $this->bodyState = $bodyState;
        $this->engineState = $engineState;
        $this->tyreState = $tyreState;
        $this->maintenanceState = $maintenanceState;
        $this->isTimingBeltChanged = $isTimingBeltChanged;
        $this->isImported = $isImported;
        $this->isFirstHand = $isFirstHand;
        $this->additionalInformation = $additionalInformation;
        $this->city = $city;
    }

    /**
     * @param Picture $picture
     */
    public function addPicture(Picture $picture): void
    {
        $this->pictures[] = $picture;
    }
}
