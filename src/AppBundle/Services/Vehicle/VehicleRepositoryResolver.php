<?php


namespace AppBundle\Services\Vehicle;


use AppBundle\Doctrine\Repository\DoctrinePersonalVehicleRepository;
use AppBundle\Doctrine\Repository\DoctrineProVehicleRepository;
use AppBundle\Doctrine\Repository\DoctrineVehicleRepository;
use AppBundle\Session\Model\SessionMessage;
use Wamcar\User\BaseUser;

class VehicleRepositoryResolver
{
    /** @var DoctrinePersonalVehicleRepository */
    protected $personalVehicleRepository;
    /** @var DoctrineProVehicleRepository */
    protected $proVehicleRepository;
    /** @var array */
    protected $vehicleRepositories;

    /**
     * VehicleRepositoryResolver constructor.
     * @param DoctrinePersonalVehicleRepository $personalVehicleRepository
     * @param DoctrineProVehicleRepository $proVehicleRepository
     * @param array $vehicleRepositories
     */
    public function __construct(
        DoctrinePersonalVehicleRepository $personalVehicleRepository,
        DoctrineProVehicleRepository $proVehicleRepository,
        array $vehicleRepositories
    )
    {
        $this->personalVehicleRepository = $personalVehicleRepository;
        $this->proVehicleRepository = $proVehicleRepository;
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

    /**
     * @param SessionMessage $sessionMessage
     * @return DoctrineVehicleRepository
     */
    public function getVehicleRepositoryByVehicleSessionMessage(SessionMessage $sessionMessage): DoctrineVehicleRepository
    {
        return $sessionMessage->isProVehicle() ? $this->proVehicleRepository : $this->personalVehicleRepository;
    }

    /**
     * @param SessionMessage $sessionMessage
     * @return DoctrineVehicleRepository
     */
    public function getVehicleRepositoryByVehicleHeaderSessionMessage(SessionMessage $sessionMessage): DoctrineVehicleRepository
    {
        return $sessionMessage->isProVehicleHeader() ? $this->proVehicleRepository : $this->personalVehicleRepository;
    }
}
