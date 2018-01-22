<?php

namespace Wamcar\Vehicle;

use Wamcar\Garage\Garage;

interface VehicleRepository
{
    /**
     * @param Vehicle $vehicle
     */
    public function add(Vehicle $vehicle): void;

    /**
     * @param Vehicle $vehicle
     */
    public function update(Vehicle $vehicle): void;

    /**
     * @param Vehicle $vehicle
     */
    public function remove(Vehicle $vehicle): void;

    /**
     * @param Garage $garage
     * @return array
     */
    public function findAllForGarage(Garage $garage): array;

    /**
     * @param Garage $garage
     */
    public function deleteAllForGarage(Garage $garage): void;

    /**
     * @param string $id
     * @return Vehicle
     */
    public function find($id);
}
