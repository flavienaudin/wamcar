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
     * @return array
     */
    public function findAllForGarage(Garage $garage): array;

    /**
     * @param string $id
     * @return Vehicle
     */
    public function find($id);

    /**
     * @return array
     */
    public function findAll(): array;
}
