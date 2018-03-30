<?php

namespace Wamcar\Garage\Event;


use Wamcar\Garage\Garage;

abstract class AbstractGarageEvent
{
    /** @var Garage $garage */
    private $garage;

    /**
     * AbstractGarageEvent constructor.
     * @param Garage $garage
     */
    public function __construct(Garage $garage)
    {
        $this->garage = $garage;
    }

    /**
     * @return Garage
     */
    public function getGarage(): Garage
    {
        return $this->garage;
    }
}