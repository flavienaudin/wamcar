<?php

namespace AppBundle\Form\DTO;

use AppBundle\Services\Vehicle\CanBeProVehicle;

final class ProVehicleDTO extends VehicleDTO implements CanBeProVehicle
{
    /** @var VehicleOfferDTO */
    public $offer;

    /**
     * VehicleDTO constructor.
     */
    public function __construct(string $registrationNumber = null)
    {
        parent::__construct($registrationNumber);
        $this->offer = new VehicleOfferDTO();
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
