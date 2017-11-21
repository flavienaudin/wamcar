<?php

namespace Wamcar\Vehicle;

use Wamcar\Garage\Garage;
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
        parent::__construct($modelVersion, $transmission, $registration, $registrationDate, $mileage, $pictures, $safetyTestDate, $safetyTestState, $bodyState, $engineState, $tyreState, $maintenanceState, $isTimingBeltChanged, $isImported, $isFirstHand, $additionalInformation);
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
