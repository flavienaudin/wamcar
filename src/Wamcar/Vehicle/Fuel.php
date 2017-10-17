<?php

namespace Wamcar\Vehicle;

final class Fuel
{
    /** @var string */
    private $name;

    /**
     * Engine constructor.
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }
}
