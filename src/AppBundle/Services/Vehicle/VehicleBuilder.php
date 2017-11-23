<?php


namespace AppBundle\Services\Vehicle;

use Wamcar\Vehicle\ProVehicle;

interface VehicleBuilder
{

    public static function buildFromDTO(CanBeProVehicle $vehicleDTO): ProVehicle;

    public static function buildUpdateFromDTO(CanBeProVehicle $vehicleDTO, ProVehicle $vehicle): ProVehicle;
}
