<?php

namespace Wamcar\Vehicle;

abstract class Picture
{
    /** @var Vehicle */
    protected $vehicle;
    /** @var ?string */
    protected $caption;

    /**
     * Picture constructor.
     * @param Vehicle $vehicle
     * @param string|null $caption
     */
    public function __construct(Vehicle $vehicle, string $caption = null)
    {
        $this->vehicle = $vehicle;
        $this->caption = $caption;
    }
}
