<?php

namespace Wamcar\Vehicle;


use Doctrine\Common\Collections\Collection;

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
}
