<?php

namespace Wamcar\Vehicle\Event;

use Wamcar\Vehicle\BaseVehicle;

abstract class AbstractVehicleEvent
{
    /** @var BaseVehicle */
    private $vehicle;

    /**
     * AbstractUserEvent constructor.
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
