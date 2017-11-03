<?php


namespace AppBundle\Services\User;


use AppBundle\Doctrine\Entity\ApplicationGarage;
use Wamcar\Garage\GarageProUser;

interface HasGarageMember
{

    /**
     * @param ApplicationGarage $garage
     * @return null|GarageProUser
     */
    public function getMemberByGarage(ApplicationGarage $garage): ?GarageProUser;

}
