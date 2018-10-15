<?php

namespace Wamcar\Vehicle;

use AppBundle\Services\User\CanBeGarageMember;
use Wamcar\Garage\Garage;
use Wamcar\Location\City;
use Wamcar\User\BaseUser;
use Wamcar\User\ProUser;
use Wamcar\Vehicle\Enum\Funding;
use Wamcar\Vehicle\Enum\Guarantee;
use Wamcar\Vehicle\Enum\MaintenanceState;
use Wamcar\Vehicle\Enum\SafetyTestState;
use Wamcar\Vehicle\Enum\TimingBeltState;
use Wamcar\Vehicle\Enum\Transmission;

class ProVehicle extends BaseVehicle
{
    const TYPE = "pro";

    /** @var float */
    private $price;
    /** @var float */
    private $catalogPrice;
    /** @var float */
    private $discount;
    /** @var Guarantee */
    private $guarantee;
    /** @var string */
    private $otherGuarantee;
    /** @var Funding */
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
        bool $isUsed,
        int $mileage,
        array $pictures,
        \DateTimeInterface $safetyTestDate = null,
        SafetyTestState $safetyTestState = null,
        int $bodyState = null,
        int $engineState = null,
        int $tyreState = null,
        MaintenanceState $maintenanceState = null,
        TimingBeltState $timingBeltState = null,
        bool $isImported = null,
        bool $isFirstHand = null,
        string $additionalInformation = null,
        City $city = null,
        float $price,
        float $catalogPrice = null,
        float $discount = null,
        Guarantee $guarantee = null,
        string $otherGuarantee = null,
        Funding $funding = null,
        string $otherFunding = null,
        string $additionalServices = null,
        string $reference = null
    )
    {
        parent::__construct($modelVersion, $transmission, $registration, $registrationDate, $isUsed, $mileage, $pictures, $safetyTestDate, $safetyTestState, $bodyState, $engineState, $tyreState, $maintenanceState, $timingBeltState, $isImported, $isFirstHand, $additionalInformation, $city);
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
     * @return City
     */
    public function getCity(): ?City
    {
        return ($this->city && $this->city->getPostalCode()) ? $this->city : $this->getGarage()->getCity();
    }

    /**
     * @return \DateTimeInterface
     */
    public function getUpdatedAt(): \DateTimeInterface
    {
        return $this->updatedAt;
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
     * @return null|Guarantee
     */
    public function getGuarantee(): ?Guarantee
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
     * @return null|Funding
     */
    public function getFunding(): ?Funding
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
    public function getGarage(): ?Garage
    {
        return $this->garage;
    }

    /**
     * @return null|string
     */
    public function getGarageName(): ?string
    {
        return $this->garage ? $this->garage->getName() : null;
    }

    /**
     * @param Garage $garage
     */
    public function setGarage(?Garage $garage): void
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
     * @param string $otherGuarantee
     */
    public function setOtherGuarantee(?string $otherGuarantee): void
    {
        $this->otherGuarantee = $otherGuarantee;
    }

    /**
     * @param string $funding
     */
    public function setFunding(?string $funding): void
    {
        $this->funding = $funding;
    }

    /**
     * @param string $otherFunding
     */
    public function setOtherFunding(?string $otherFunding): void
    {
        $this->otherFunding = $otherFunding;
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
     * // TODO Implémenter l'assignation des véhicules à son vendeur
     * @return ProUser
     */
    public function getSeller(): ?ProUser
    {
        return $this->getGarage()->getSeller();
    }


    /**
     * @param BaseUser|null $user
     * @return bool
     */
    public function canEditMe(BaseUser $user = null): bool
    {
        return $user instanceof CanBeGarageMember && $user->isMemberOfGarage($this->getGarage());
    }
}
