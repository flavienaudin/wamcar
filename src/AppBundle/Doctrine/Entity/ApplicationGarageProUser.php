<?php


namespace AppBundle\Doctrine\Entity;


use Wamcar\Garage\Garage;
use Wamcar\Garage\GarageProUser;
use Wamcar\User\ProUser;

class ApplicationGarageProUser extends GarageProUser
{

    /**
     * ApplicationGarageProUser constructor.
     * @param Garage $garage
     * @param ProUser $proUser
     */
    public function __construct(
        Garage $garage,
        ProUser $proUser
    )
    {
        parent::__construct(
            $garage,
            $proUser
        );
    }
}
