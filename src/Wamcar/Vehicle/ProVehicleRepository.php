<?php

namespace Wamcar\Vehicle;


use Doctrine\Common\Collections\Collection;
use Wamcar\Garage\Garage;

interface ProVehicleRepository extends VehicleRepository
{
    /**
     * @param $reference
     * @return ProVehicle|null
     */
    public function findByReference($reference);


    /**
     * @param Garage $garage
     * @param array $orderBy
     * @param null|bool $ignoreSoftDeleted
     * @return array
     */
    public function findByGarage(Garage $garage, array $orderBy = [], bool $ignoreSoftDeleted = false): array;
}
