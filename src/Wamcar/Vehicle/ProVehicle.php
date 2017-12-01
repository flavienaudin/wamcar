<?php

namespace Wamcar\Vehicle;

use Wamcar\Garage\Garage;
use Wamcar\Location\City;
use Wamcar\User\ProUser;
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
    /** @var string */
    private $otherGuarantee;
    /** @var string */
    private $funding;
    /** @var string */
    private $otherFunding;
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
        string $otherGuarantee = null,
        string $funding = null,
        string $otherFunding = null,
        string $additionalServices = null,
        string $reference = null
    )
    {
        parent::__construct($modelVersion, $transmission, $registration, $registrationDate, $mileage, $pictures, $safetyTestDate, $safetyTestState, $bodyState, $engineState, $tyreState, $maintenanceState, $isTimingBeltChanged, $isImported, $isFirstHand, $additionalInformation, $city);
        $this->price = $price;
        $this->catalogPrice = $catalogPrice;
        $this->discount = $discount;
        $this->guarantee = $guarantee;
        $this->otherGuarantee = $otherGuarantee;
        $this->funding = $funding;
        $this->otherFunding = $otherFunding;
        $this->additionalServices = $additionalServices;
        $this->reference = $reference;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->getMake() . ' ' . $this->modelVersion->getName();
    }

    /**
     * @return string
     */
    public function getPostalCode(): string
    {
        return $this->city->getPostalCode();
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
    public function getModelName(): string
    {
        return $this->modelVersion->getModel()->getName();
    }

    /**
     * @return string
     */
    public function getModelVersionName(): string
    {
        return $this->modelVersion->getName();
    }

    /**
     * @return string
     */
    public function getEngineName(): string
    {
        return $this->modelVersion->getEngine()->getName();
    }

    /**
     * @return string
     */
    public function getFuelName(): string
    {
        return $this->modelVersion->getEngine()->getFuel()->getName();
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
    public function getCatalogPrice(): ?float
    {
        return $this->catalogPrice;
    }

    /**
     * @return float
     */
    public function getDiscount(): ?float
    {
        return $this->discount;
    }

    /**
     * @return string
     */
    public function getGuarantee(): ?string
    {
        return $this->guarantee;
    }

    /**
     * @return string
     */
    public function getOtherGuarantee(): ?string
    {
        return $this->otherGuarantee;
    }

    /**
     * @return string
     */
    public function getFunding(): ?string
    {
        return $this->funding;
    }

    /**
     * @return string
     */
    public function getOtherFunding(): ?string
    {
        return $this->otherFunding;
    }

    /**
     * @return string
     */
    public function getAdditionalServices(): ?string
    {
        return $this->additionalServices;
    }

    /**
     * @return string
     */
    public function getReference(): ?string
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

    /**
     * @param float $price
     */
    public function setPrice(float $price): void
    {
        $this->price = $price;
    }

    /**
     * @param float $catalogPrice
     */
    public function setCatalogPrice(?float $catalogPrice): void
    {
        $this->catalogPrice = $catalogPrice;
    }

    /**
     * @param float $discount
     */
    public function setDiscount(?float $discount): void
    {
        $this->discount = $discount;
    }

    /**
     * @param string $guarantee
     */
    public function setGuarantee(?string $guarantee): void
    {
        $this->guarantee = $guarantee;
    }

    /**
     * @param bool $refunded
     */
    public function setRefunded(bool $refunded): void
    {
        $this->refunded = $refunded;
    }

    /**
     * @param string $otherGuarantee
     */
    public function setOtherGuarantee(?string $otherGuarantee): void
    {
        $this->otherGuarantee = $otherGuarantee;
    }

    /**
     * @param string $additionalServices
     */
    public function setAdditionalServices(?string $additionalServices): void
    {
        $this->additionalServices = $additionalServices;
    }

    /**
     * @param string $reference
     */
    public function setReference(?string $reference): void
    {
        $this->reference = $reference;
    }

    /**
     * @return Garage
     */
    public function getSeller(): ProUser
    {
        return $this->getGarage()->getSeller();
    }
}
