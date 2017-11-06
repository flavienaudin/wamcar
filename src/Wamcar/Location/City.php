<?php

namespace Wamcar\Location;

class City
{
    /** @var string */
    private $postalCode;
    /** @var string */
    private $name;

    /**
     * City constructor.
     * @param string $postalCode
     * @param string $name
     */
    public function __construct(string $postalCode, string $name)
    {
        $this->postalCode = $postalCode;
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getPostalCode(): string
    {
        return $this->postalCode;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

}
