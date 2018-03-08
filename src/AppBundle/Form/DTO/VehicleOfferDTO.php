<?php

namespace AppBundle\Form\DTO;

use Wamcar\Vehicle\Enum\Funding;
use Wamcar\Vehicle\Enum\Guarantee;

class VehicleOfferDTO
{
    /** @var float */
    public $price;
    /** @var float */
    public $catalogPrice;
    /** @var float */
    public $discount;
    /** @var Guarantee */
    public $guarantee;
    /** @var string */
    public $otherGuarantee;
    /** @var Funding */
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
     * @param $otherGuarantee
     * @param $funding
     * @param $otherFunding
     * @param $additionalServices
     * @param $reference
     * @return VehicleOfferDTO
     */
    public static function buildFromOffer(
        $price,
        $catalogPrice,
        $discount,
        $guarantee,
        $otherGuarantee,
        $funding,
        $otherFunding,
        $additionalServices,
        $reference
    )
    {
        $dto = new self();
        $dto->price = $price;
        $dto->catalogPrice = $catalogPrice;
        $dto->discount = $discount;
        $dto->guarantee = $guarantee;
        $dto->otherGuarantee = $otherGuarantee;
        $dto->funding = $funding;
        $dto->otherFunding = $otherFunding;
        $dto->additionalServices = $additionalServices;
        $dto->reference = $reference;

        return $dto;
    }
}
