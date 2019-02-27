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
     * Return the $limit last vehicles
     * @param $limit
     * @return Collection
     */
    public function getLast($limit);

    /**
     * @param Garage $garage
     * @param null|array $orderBy
     * @param null|bool $ignoreSoftDeleted
     * @return array
     */
    public function findAllForGarage(Garage $garage, array $orderBy = [], bool $ignoreSoftDeleted = false): array;
}
