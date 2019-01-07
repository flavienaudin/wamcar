<?php

namespace AppBundle\Exception\Garage;


use Wamcar\Garage\Garage;

class GarageException extends \Exception
{
    /** @var Garage $garage */
    private $garage;

    /**
     * AlreadyGarageMember constructor.
     * @param Garage $garage
     */
    public function __construct(Garage $garage)
    {
        $this->garage = $garage;
    }

    /**
     * @return Garage
     */
    public function getGarage(): Garage
    {
        return $this->garage;
    }
}