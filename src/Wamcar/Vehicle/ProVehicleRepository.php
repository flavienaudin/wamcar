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
     * @param array $orderBy
     * @return Collection
     */
    public function getByGarage(Garage $garage, array $orderBy = []);

    /**
     * @param  Garage $garage
     * @param array $references
     * @return Collection|array
     */
    public function findByGarageAndExcludedReferences(Garage $garage, array $references);
}
