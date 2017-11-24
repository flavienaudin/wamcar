<?php

namespace Wamcar\Vehicle;

final class Engine
{
    /** @var string */
    private $name;
    /** @var Fuel */
    private $fuel;

    /**
     * Engine constructor.
     * @param string $name
     * @param Fuel $fuel
     */
    public function __construct(string $name, Fuel $fuel)
    {
        $this->name = $name;
        $this->fuel = $fuel;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return Fuel
     */
    public function getFuel(): Fuel
    {
        return $this->fuel;
    }
}
