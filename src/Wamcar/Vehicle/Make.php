<?php

namespace Wamcar\Vehicle;

final class Make
{
    /** @var string */
    private $name;
    /** @var Model[]|array */
    private $models;

    /**
     * Make constructor.
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }
}
