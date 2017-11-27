<?php

namespace Wamcar\Location;

class City
{
    /** @var string */
    private $postalCode;
    /** @var string */
    private $name;
    /** @var string */
    private $latitude;
    /** @var string */
    private $longitude;


    /**
     * City constructor.
     * @param string $postalCode
     * @param string $name
     * @param string|null $latitude
     * @param string|null $longitude
     *
     */
    public function __construct(string $postalCode, string $name, string $latitude = null, string $longitude = null)
    {
        $this->postalCode = $postalCode;
        $this->name = $name;
        $this->latitude = $latitude;
        $this->longitude = $longitude;
    }

    /**
     * @return string
     */
    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    /**
     * @return string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getLatitude(): ?string
    {
        return $this->latitude;
    }

    /**
     * @return string
     */
    public function getLongitude(): ?string
    {
        return $this->longitude;
    }

}
