<?php


namespace AppBundle\Services\User;


use Wamcar\Garage\Garage;
use Wamcar\Garage\GarageProUser;

interface HasGarageMembership
{

    /**
     * @param Garage $garage
     * @return null|GarageProUser
     */
    public function getMembershipByGarage(Garage $garage): ?GarageProUser;

}
