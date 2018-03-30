<?php

namespace Wamcar\Garage\Event;


use Wamcar\Garage\Garage;

interface GarageEvent
{
    /**
     * GarageEvent constructor.
     * @param Garage $garage
     */
    public function __construct(Garage $garage);

    /**
     * @return Garage
     */
    public function getGarage(): Garage;

}