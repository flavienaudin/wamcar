<?php

namespace Wamcar\Vehicle;

use AppBundle\Services\User\CanBeGarageMember;
use Wamcar\Garage\Garage;
use Wamcar\Location\City;
use Wamcar\Sale\Declaration;
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
    /** @var null|Garage */
    private $garage;
    /** @var Declaration|null */
    private $saleDeclaration;



    /**
     * ProVehicle constructor
     * NOTE : Garage must be set manually just after the creation
     *
     * @param ModelVersion $modelVersion
     * @param Transmission $transmission
     * @param Registration|null $registration
     * @param \DateTimeInterface $registrationDate
     * @param bool $isUsed
     * @param int $mileage
     * @param array $pictures
     * @param \DateTimeInterface|null $safetyTestDate
     * @param SafetyTestState|null $safetyTestState
     * @param int|null $bodyState
     * @param int|null $engineState
     * @param int|null $tyreState
     * @param MaintenanceState|null $maintenanceState
     * @param TimingBeltState|null $timingBeltState
     * @param bool|null $isImported
     * @param bool|null $isFirstHand
     * @param string|null $additionalInformation
     * @param City|null $city
     * @param float $price
     * @param float|null $catalogPrice
     * @param float|null $discount
     * @param Guarantee|null $guarantee
     * @param string|null $otherGuarantee
     * @param Funding|null $funding
     * @param string|null $otherFunding
     * @param string|null $additionalServices
     * @param string|null $reference
     * @throws \Exception
     */
    public function __construct(
        ModelVersion $modelVersion,
        Transmission $transmission,
        ?Registration $registration,
        \DateTimeInterface $registrationDate,
        bool $isUsed,
        int $mileage,
        array $pictures,
        ?\DateTimeInterface $safetyTestDate,
        ?SafetyTestState $safetyTestState,
        ?int $bodyState,
        ?int $engineState,
        ?int $tyreState,
        ?MaintenanceState $maintenanceState,
        ?TimingBeltState $timingBeltState,
        ?bool $isImported,
        ?bool $isFirstHand,
        ?string $additionalInformation,
        ?City $city,
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
     * @param bool $onlyMaxScore
     * @param BaseUser|null $userVisiting
     * @return array
     */
    public function getSuggestedSellers(bool $onlyMaxScore, ?BaseUser $userVisiting = null): array
    {
        return $this->garage->getBestSellersForVehicle($this, $onlyMaxScore, $userVisiting);
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
     * @return Declaration|null
     */
    public function getSaleDeclaration(): ?Declaration
    {
        return $this->saleDeclaration;
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
     * @param Guarantee $guarantee
     */
    public function setGuarantee(?Guarantee $guarantee): void
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
     * @param Declaration|null $saleDeclaration
     */
    public function setSaleDeclaration(?Declaration $saleDeclaration): void
    {
        $this->saleDeclaration = $saleDeclaration;
    }

    /**
     * @param BaseUser|null $user
     * @return bool
     */
    public function canEditMe(BaseUser $user = null): bool
    {
        return $user instanceof CanBeGarageMember && $user->isMemberOfGarage($this->getGarage());
    }


    /**
     * @param BaseUser|null $user
     * @return bool
     */
    public function canDeclareSale(BaseUser $user = null): bool
    {
        return $user instanceof CanBeGarageMember && $user->isMemberOfGarage($this->getGarage());;
    }
}
