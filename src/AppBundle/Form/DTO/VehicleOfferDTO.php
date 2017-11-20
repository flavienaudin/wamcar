<?php

namespace AppBundle\Form\DTO;

use Symfony\Component\HttpFoundation\File\File;

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
    public $references;
}
