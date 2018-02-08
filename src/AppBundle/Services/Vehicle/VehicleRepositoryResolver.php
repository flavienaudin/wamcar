<?php


namespace AppBundle\Services\Vehicle;


use AppBundle\Doctrine\Repository\DoctrineVehicleRepository;
use Wamcar\User\BaseUser;

class VehicleRepositoryResolver
{
    /** @var array */
    protected $vehicleRepositories;

    public function __construct(
        array $vehicleRepositories
    )
    {
        $this->vehicleRepositories = $vehicleRepositories;
    }

    /**
     * @param BaseUser $user
     * @return DoctrineVehicleRepository
     */
    public function getVehicleRepositoryByUser(BaseUser $user): DoctrineVehicleRepository
    {
        return $this->vehicleRepositories[get_class($user)];
    }
}
