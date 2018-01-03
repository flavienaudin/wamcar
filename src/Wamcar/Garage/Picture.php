<?php

namespace Wamcar\Garage;

abstract class Picture
{
    /** @var Garage */
    protected $garage;

    /**
     * Picture constructor.
     * @param Garage $garage
     */
    public function __construct(Garage $garage)
    {
        $this->garage = $garage;
    }
}
