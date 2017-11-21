<?php

namespace Wamcar\Vehicle;

use Wamcar\Garage\Garage;
use Wamcar\Location\City;
use Wamcar\Vehicle\Enum\MaintenanceState;
use Wamcar\Vehicle\Enum\SafetyTestDate;
use Wamcar\Vehicle\Enum\SafetyTestState;
use Wamcar\Vehicle\Enum\Transmission;

class ProVehicle extends BaseVehicle
{

    /** @var float */
    private $price;
    /** @var float */
    private $catalogPrice;
    /** @var float */
    private $discount;
    /** @var string */
    private $guarantee;
    /** @var bool */
    private $refunded;
    /** @var string */
    private $otherGuarantee;
    /** @var string */
    private $additionalServices;
    /** @var string */
    private $reference;
    /** @var Garage */
    private $garage;

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
        City $city,
        float $price,
        float $catalogPrice = null,
        float $discount = null,
        string $guarantee = null,
        bool $refunded = false,
        string $otherGuarantee = null,
        string $additionalServices = null,
        string $reference = null
    )
    {
        parent::__construct($modelVersion, $transmission, $registration, $registrationDate, $mileage, $pictures, $safetyTestDate, $safetyTestState, $bodyState, $engineState, $tyreState, $maintenanceState, $isTimingBeltChanged, $isImported, $isFirstHand, $additionalInformation, $city);
        $this->price = $price;
        $this->catalogPrice = $catalogPrice;
        $this->discount = $discount;
        $this->guarantee = $guarantee;
        $this->refunded = $refunded;
        $this->otherGuarantee = $otherGuarantee;
        $this->additionalServices = $additionalServices;
        $this->reference = $reference;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->modelVersion->getName();
    }

    /**
     * @return string
     */
    public function getCityName(): string
    {
        return $this->city->getName();
    }

    /**
     * @return string
     */
    public function getMake(): string
    {
        return $this->modelVersion->getModel()->getMake()->getName();
    }

    /**
     * @return string
     */
    public function getModel(): string
    {
        return $this->modelVersion->getModel()->getName();
    }

    /**
     * @return string
     */
    public function getYears(): string
    {
        return $this->getRegistrationDate()->format('Y');
    }

    /**
     * @return Transmission
     */
    public function getTransmission(): Transmission
    {
        return $this->transmission;
    }

    /**
     * @return null|Registration
     */
    public function getRegistration(): ?Registration
    {
        return $this->registration;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getRegistrationDate(): \DateTimeInterface
    {
        return $this->registrationDate;
    }

    /**
     * @return int
     */
    public function getMileage(): int
    {
        return $this->mileage;
    }

    /**
     * @return array|Picture[]
     */
    public function getPictures()
    {
        return $this->pictures;
    }

    /**
     * @return SafetyTestDate
     */
    public function getSafetyTestDate(): SafetyTestDate
    {
        return $this->safetyTestDate;
    }

    /**
     * @return SafetyTestState
     */
    public function getSafetyTestState(): SafetyTestState
    {
        return $this->safetyTestState;
    }

    /**
     * @return int
     */
    public function getBodyState(): int
    {
        return $this->bodyState;
    }

    /**
     * @return int|null
     */
    public function getEngineState(): ?int
    {
        return $this->engineState;
    }

    /**
     * @return int|null
     */
    public function getTyreState(): ?int
    {
        return $this->tyreState;
    }

    /**
     * @return MaintenanceState
     */
    public function getMaintenanceState(): MaintenanceState
    {
        return $this->maintenanceState;
    }

    /**
     * @return bool|null
     */
    public function getisTimingBeltChanged(): ?bool
    {
        return $this->isTimingBeltChanged;
    }

    /**
     * @return bool|null
     */
    public function getisImported(): ?bool
    {
        return $this->isImported;
    }

    /**
     * @return bool|null
     */
    public function getisFirstHand(): ?bool
    {
        return $this->isFirstHand;
    }

    /**
     * @return null|string
     */
    public function getAdditionalInformation(): ?string
    {
        return $this->additionalInformation;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    /**
     * @return float
     */
    public function getPrice(): float
    {
        return $this->price;
    }

    /**
     * @return float
     */
    public function getCatalogPrice(): float
    {
        return $this->catalogPrice;
    }

    /**
     * @return float
     */
    public function getDiscount(): float
    {
        return $this->discount;
    }

    /**
     * @return string
     */
    public function getGuarantee(): string
    {
        return $this->guarantee;
    }

    /**
     * @return bool
     */
    public function isRefunded(): bool
    {
        return $this->refunded;
    }

    /**
     * @return string
     */
    public function getOtherGuarantee(): string
    {
        return $this->otherGuarantee;
    }

    /**
     * @return string
     */
    public function getAdditionalServices(): string
    {
        return $this->additionalServices;
    }

    /**
     * @return string
     */
    public function getReference(): string
    {
        return $this->reference;
    }

    /**
     * @return Garage
     */
    public function getGarage(): Garage
    {
        return $this->garage;
    }

    /**
     * @param Garage $garage
     */
    public function setGarage(Garage $garage): void
    {
        $this->garage = $garage;
    }
}
