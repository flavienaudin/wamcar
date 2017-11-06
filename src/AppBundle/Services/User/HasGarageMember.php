<?php


namespace AppBundle\Services\User;


use Wamcar\Garage\Garage;
use Wamcar\Garage\GarageProUser;

interface HasGarageMember
{

    /**
     * @param Garage $garage
     * @return null|GarageProUser
     */
    public function getMemberByGarage(Garage $garage): ?GarageProUser;

}
