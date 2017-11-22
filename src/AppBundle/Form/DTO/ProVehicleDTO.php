<?php

namespace AppBundle\Form\DTO;

use AppBundle\Services\Vehicle\CanBeProVehicle;
use Wamcar\Vehicle\ProVehicle;

final class ProVehicleDTO extends VehicleDTO implements CanBeProVehicle
{
    /** @var VehicleOfferDTO */
    public $offer;

    /**
     * ProVehicleDTO constructor.
     * @param ProVehicle|null $vehicle
     * @param string|null $registrationNumber
     */
    public function __construct(ProVehicle $vehicle = null, string $registrationNumber = null)
    {
        parent::__construct($vehicle, $registrationNumber);
        $this->offer = new VehicleOfferDTO($vehicle);
    }

    /**
     * @return float
     */
    public function getPrice(): ?float
    {
        return $this->offer->price;
    }

    /**
     * @return null|float
     */
    public function getCatalogPrice(): ?float
    {
        return $this->offer->catalogPrice;
    }

    /**
     * @return null|float
     */
    public function getDiscount(): ?float
    {
        return $this->offer->discount;
    }

    /**
     * @return string
     */
    public function getGuarantee(): ?string
    {
        return $this->offer->guarantee;
    }

    /**
     * @return bool
     */
    public function isRefunded(): bool
    {
        return $this->offer->refunded;
    }

    /**
     * @return string
     */
    public function getOtherGuarantee(): ?string
    {
        return $this->offer->otherGuarantee;
    }

    /**
     * @return string
     */
    public function getAdditionalServices(): ?string
    {
        return $this->offer->additionalServices;
    }

    /**
     * @return string
     */
    public function getReference(): ?string
    {
        return $this->offer->reference;
    }

}
