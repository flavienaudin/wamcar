<?php

namespace Wamcar\Garage;

abstract class Picture
{
    /** @var null|Garage */
    protected $garage;

    /**
     * Picture constructor.
     * @param Garage $garage
     */
    public function __construct(Garage $garage)
    {
        $this->garage = $garage;
    }

    /**
     * @param Garage $garage
     */
    public function setGarage(?Garage $garage): void
    {
        $this->garage = $garage;
    }
}
