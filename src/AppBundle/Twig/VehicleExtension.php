<?php


namespace AppBundle\Twig;

use AppBundle\Doctrine\Repository\DoctrinePersonalVehicleRepository;
use AppBundle\Doctrine\Repository\DoctrineProVehicleRepository;
use AppBundle\Services\Vehicle\VehicleRepositoryResolver;
use Twig\Extension\AbstractExtension;
use Wamcar\User\BaseUser;
use Wamcar\Vehicle\BaseVehicle;

class VehicleExtension extends AbstractExtension
{
    /** @var DoctrineProVehicleRepository */
    protected $proVehicleRepository;
    /** @var DoctrinePersonalVehicleRepository */
    protected $personalVehicleRepository;
    /** @var VehicleRepositoryResolver */
    protected $vehicleRepositoryResolver;

    public function __construct(
        DoctrineProVehicleRepository $proVehicleRepository,
        DoctrinePersonalVehicleRepository $personalVehicleRepository,
        VehicleRepositoryResolver $vehicleRepositoryResolver
    )
    {
        $this->proVehicleRepository = $proVehicleRepository;
        $this->personalVehicleRepository = $personalVehicleRepository;
        $this->vehicleRepositoryResolver = $vehicleRepositoryResolver;
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('getVehicle', array($this, 'getVehicleFunction'))
        );
    }

    /**
     * @param string $vehicleId
     * @param null|BaseUser $user
     * @return null|BaseVehicle
     */
    public function getVehicleFunction(string $vehicleId, ?BaseUser $user = null): ?BaseVehicle
    {
        if ($user) {
            $repo = $this->vehicleRepositoryResolver->getVehicleRepositoryByUser($user);
            return $repo->find($vehicleId);
        }

        $vehicle = $this->proVehicleRepository->find($vehicleId);
        return $vehicle ? $vehicle : $this->personalVehicleRepository->find($vehicleId);
    }
}
