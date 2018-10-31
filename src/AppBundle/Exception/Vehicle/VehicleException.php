<?php

namespace AppBundle\Exception\Vehicle;


use Wamcar\Vehicle\BaseVehicle;

class VehicleException extends \Exception
{
    /** @var BaseVehicle $vehicle */
    private $vehicle;

    /**
     * VehicleException constructor.
     * @param BaseVehicle $vehicle
     */
    public function __construct(BaseVehicle $vehicle)
    {
        $this->vehicle = $vehicle;
    }

    /**
     * @return BaseVehicle
     */
    public function getVehicle(): BaseVehicle
    {
        return $this->vehicle;
    }
}