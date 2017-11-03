<?php


namespace Wamcar\Garage;


use Wamcar\User\ProUser;

class GarageProUser
{
    /** @var  Garage */
    protected $garage;
    /** @var  ProUser */
    protected $proUser;

    /**
     * GarageProUser constructor.
     * @param Garage $garage
     * @param ProUser $proUser
     */
    public function __construct(Garage $garage, ProUser $proUser)
    {
        $this->garage = $garage;
        $this->proUser = $proUser;
    }

    /**
     * @return Garage
     */
    public function getGarage(): Garage
    {
        return $this->garage;
    }

    /**
     * @return ProUser
     */
    public function getProUser(): ProUser
    {
        return $this->proUser;
    }
}
