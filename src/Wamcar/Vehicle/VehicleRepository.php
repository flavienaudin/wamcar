<?php

namespace Wamcar\Vehicle;

interface VehicleRepository
{
    /**
     * @param Vehicle $vehicle
     */
    public function add(Vehicle $vehicle): void;
}
