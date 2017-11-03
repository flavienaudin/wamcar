<?php


namespace AppBundle\Doctrine\Entity;


use Wamcar\Garage\Address;
use Wamcar\Garage\Garage;

class ApplicationGarage extends Garage
{

    /**
     * ApplicationGarage constructor.
     * @param string $name
     * @param string $siren
     * @param null|string $openingHours
     * @param null|string $presentation
     * @param Address $address
     */
    public function __construct(
        string $name,
        string $siren,
        ?string $openingHours,
        ?string $presentation,
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
