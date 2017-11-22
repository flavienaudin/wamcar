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
     * @param City $city
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
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param Picture $picture
     */
    public function addPicture(Picture $picture): void
    {
        $this->pictures[] = $picture;
    }

    /**
     * @param ModelVersion $modelVersion
     */
    public function setModelVersion(ModelVersion $modelVersion): void
    {
        $this->modelVersion = $modelVersion;
    }

    /**
     * @param Transmission $transmission
     */
    public function setTransmission(Transmission $transmission): void
    {
        $this->transmission = $transmission;
    }

    /**
     * @param null|Registration $registration
     */
    public function setRegistration(?Registration $registration): void
    {
        $this->registration = $registration;
    }

    /**
     * @param \DateTimeInterface $registrationDate
     */
    public function setRegistrationDate(\DateTimeInterface $registrationDate): void
    {
        $this->registrationDate = $registrationDate;
    }

    /**
     * @param int $mileage
     */
    public function setMileage(int $mileage): void
    {
        $this->mileage = $mileage;
    }

    /**
     * @param array|Picture[] $pictures
     */
    public function setPictures($pictures): void
    {
        $this->pictures = $pictures;
    }

    /**
     * @param SafetyTestDate $safetyTestDate
     */
    public function setSafetyTestDate(SafetyTestDate $safetyTestDate): void
    {
        $this->safetyTestDate = $safetyTestDate;
    }

    /**
     * @param SafetyTestState $safetyTestState
     */
    public function setSafetyTestState(SafetyTestState $safetyTestState): void
    {
        $this->safetyTestState = $safetyTestState;
    }

    /**
     * @param int $bodyState
     */
    public function setBodyState(int $bodyState): void
    {
        $this->bodyState = $bodyState;
    }

    /**
     * @param int|null $engineState
     */
    public function setEngineState(?int $engineState): void
    {
        $this->engineState = $engineState;
    }

    /**
     * @param int|null $tyreState
     */
    public function setTyreState(?int $tyreState): void
    {
        $this->tyreState = $tyreState;
    }

    /**
     * @param MaintenanceState $maintenanceState
     */
    public function setMaintenanceState(MaintenanceState $maintenanceState): void
    {
        $this->maintenanceState = $maintenanceState;
    }

    /**
     * @param bool|null $isTimingBeltChanged
     */
    public function setIsTimingBeltChanged(?bool $isTimingBeltChanged): void
    {
        $this->isTimingBeltChanged = $isTimingBeltChanged;
    }

    /**
     * @param bool|null $isImported
     */
    public function setIsImported(?bool $isImported): void
    {
        $this->isImported = $isImported;
    }

    /**
     * @param bool|null $isFirstHand
     */
    public function setIsFirstHand(?bool $isFirstHand): void
    {
        $this->isFirstHand = $isFirstHand;
    }

    /**
     * @param null|string $additionalInformation
     */
    public function setAdditionalInformation(?string $additionalInformation): void
    {
        $this->additionalInformation = $additionalInformation;
    }

    /**
     * @param City $city
     */
    public function setCity(City $city): void
    {
        $this->city = $city;
    }
}
