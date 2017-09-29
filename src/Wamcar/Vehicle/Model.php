<?php

namespace Wamcar\Vehicle;

final class Model
{
    /** @var $name */
    private $name;
    /** @var Make */
    private $make;
    /** @var ModelVersion[]|array */
    private $versions;

    /**
     * Model constructor.
     * @param $name
     * @param Make $brand
     */
    public function __construct(string $name, Make $make)
    {
        $this->name = $name;
        $this->make = $make;
    }
}
