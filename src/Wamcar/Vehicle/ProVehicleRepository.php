<?php

namespace Wamcar\Vehicle;


interface ProVehicleRepository extends VehicleRepository
{
    /**
     * @param $reference
     * @return ProVehicle|null
     */
    public function findByReference($reference);
}
