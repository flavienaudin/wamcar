<?php

namespace AppBundle\Form\DTO;

use Symfony\Component\HttpFoundation\File\File;
use Wamcar\Vehicle\ProVehicle;

class VehicleOfferDTO
{
    /** @var float */
    public $price;
    /** @var float */
    public $catalogPrice;
    /** @var float */
    public $discount;
    /** @var string */
    public $guarantee;
    /** @var bool */
    public $refunded;
    /** @var string */
    public $otherGuarantee;
    /** @var string */
    public $additionalServices;
    /** @var string */
    public $reference;

    public function __construct(ProVehicle $vehicle = null)
    {
        if ($vehicle) {
            $this->price = $vehicle->getPrice();
            $this->catalogPrice = $vehicle->getCatalogPrice();
            $this->discount = $vehicle->getDiscount();
            $this->guarantee = $vehicle->getGuarantee();
            $this->refunded = $vehicle->isRefunded();
            $this->otherGuarantee = $vehicle->getOtherGuarantee();
            $this->additionalServices = $vehicle->getAdditionalServices();
            $this->reference = $vehicle->getReference();
        }
    }
}
