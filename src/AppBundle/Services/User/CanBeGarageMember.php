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
    public function isMemberOfGarage(Garage $garage): bool;

    /**
     * @param Garage $garage
     * @return bool
     */
    public function isAdministratorOfGarage(Garage $garage): bool;

    /**
     * Decide if ProUser has at least ont garage
     * @return bool
     */
    public function hasGarage(): bool;

}
