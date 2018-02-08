<?php


namespace AppBundle\Services\Vehicle;


use Wamcar\User\BaseUser;
use Wamcar\Vehicle\Vehicle;

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
     * @param string $vehicleId
     * @param BaseUser $user
     * @return null|Vehicle
     */
    public function getVehicleByIdAndUser(string $vehicleId, BaseUser $user): ?Vehicle
    {
        $repo = $this->vehicleRepositories[get_class($user)];
        $vehicle = $repo->find($vehicleId);

        if ($vehicle instanceof Vehicle && $vehicle->canEditMe($user)) {
            return $vehicle;
        }

        return null;
    }
}
