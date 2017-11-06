<?php

namespace AppBundle\Doctrine\Entity;

use Gedmo\SoftDeleteable\Traits\SoftDeleteable;
use Wamcar\Garage\Address;
use Wamcar\Garage\Garage;

class ApplicationGarage extends Garage
{
    use SoftDeleteable;

    /** @var string */
    protected $deletedAt;


    /**
     * ApplicationGarage constructor.
     * @param string $name
     * @param string $siren
     * @param string|null $openingHours
     * @param string|null $presentation
     * @param Address $address
     */
    public function __construct(
        string $name,
        string $siren,
        string $openingHours = null,
        string $presentation = null,
        Address $address
    )
    {
        parent::__construct(
            $name,
            $siren,
            $openingHours,
            $presentation,
            $address
            );

    }
}
