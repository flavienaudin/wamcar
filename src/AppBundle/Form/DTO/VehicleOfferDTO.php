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
    /** @var string */
    public $otherGuarantee;
    /** @var string */
    public $funding;
    /** @var string */
    public $otherFunding;
    /** @var string */
    public $additionalServices;
    /** @var string */
    public $reference;

    /**
     * @param $price
     * @param $catalogPrice
     * @param $discount
     * @param $guarantee
     * @param $refunded
     * @param $otherGuarantee
     * @param $additionalServices
     * @param $reference
     * @return VehicleOfferDTO
     */
    public static function buildFromOffer(
        $price,
        $catalogPrice,
        $discount,
        $guarantee,
        $refunded,
        $otherGuarantee,
        $additionalServices,
        $reference
    )
    {
        $dto = new self();
        $dto->price = $price;
        $dto->catalogPrice = $catalogPrice;
        $dto->discount = $discount;
        $dto->guarantee = $guarantee;
        $dto->refunded = $refunded;
        $dto->otherGuarantee = $otherGuarantee;
        $dto->additionalServices = $additionalServices;
        $dto->reference = $reference;

        return $dto;
    }
}
