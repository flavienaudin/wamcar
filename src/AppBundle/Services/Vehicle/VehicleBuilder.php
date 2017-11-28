<?php


namespace AppBundle\Services\Vehicle;

use Wamcar\Vehicle\ProVehicle;

interface VehicleBuilder
{

    public static function newVehicleFromDTO(CanBeProVehicle $vehicleDTO): ProVehicle;

    public static function editVehicleFromDTO(CanBeProVehicle $vehicleDTO, ProVehicle $vehicle): ProVehicle;
}
