<?php


namespace AppBundle\Services\User;


use Wamcar\Garage\Garage;
use Wamcar\Garage\GarageProUser;

interface CanBeGarageMember
{

    /**
     * @param Garage $garage
     * @return null|GarageProUser
     */
    public function getMembershipByGarage(Garage $garage): ?GarageProUser;

    /**
     * @param Garage $garage
     * @return bool
     */
    public function isMembershipOfGarage(Garage $garage): bool;

}
