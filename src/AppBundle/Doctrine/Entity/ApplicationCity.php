<?php


namespace AppBundle\Doctrine\Entity;


use Wamcar\Location\City;

class ApplicationCity
{

    /** @var null|string */
    protected $insee;
    /** @var null|City */
    protected $city;
    /** @var null|string */
    protected $codeDepartement;
    /** @var null|string */
    protected $codeRegion;
    /** @var null|string */
    protected $departement;
    /** @var null|string */
    protected $region;

    /**
     * @return null|string
     */
    public function getInsee(): ?string
    {
        return $this->insee;
    }

    /**
     * @return null|City
     */
    public function getCity(): ?City
    {
        return $this->city;
    }

    /**
     * @return null|string
     */
    public function getCodeDepartement(): ?string
    {
        return $this->codeDepartement;
    }

    /**
     * @return null|string
     */
    public function getCodeRegion(): ?string
    {
        return $this->codeRegion;
    }

    /**
     * @return null|string
     */
    public function getDepartement(): ?string
    {
        return $this->departement;
    }

    /**
     * @return null|string
     */
    public function getRegion(): ?string
    {
        return $this->region;
    }
}
