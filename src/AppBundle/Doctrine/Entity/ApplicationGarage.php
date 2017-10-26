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
     * @param string $phone
     * @param string $email
     * @param null|string $openingHours
     * @param null|string $presentation
     * @param null|string $benefit
     * @param Address $address
     */
    public function __construct(
        string $name,
        string $siren,
        string $phone,
        string $email,
        ?string $openingHours,
        ?string $presentation,
        ?string $benefit,
        Address $address
    )
    {
        parent::__construct(
            $name,
            $siren,
            $phone,
            $email,
            $openingHours,
            $presentation,
            $benefit,
            $address
        );
    }
}
