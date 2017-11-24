<?php


namespace Wamcar\Vehicle\Event;


use Wamcar\Vehicle\BaseVehicle;

interface VehicleEvent
{
    /**
     * UserEvent constructor.
     * @param BaseVehicle $vehicle
     */
    public function __construct(BaseVehicle $vehicle);

    /**
     * @return BaseVehicle
     */
    public function getVehicle(): BaseVehicle;
}
