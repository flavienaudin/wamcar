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
     * @param Make $make
     */
    public function __construct(string $name, Make $make)
    {
        $this->name = $name;
        $this->make = $make;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return Make
     */
    public function getMake(): Make
    {
        return $this->make;
    }

    /**
     * @return array|ModelVersion[]
     */
    public function getVersions()
    {
        return $this->versions;
    }

}
