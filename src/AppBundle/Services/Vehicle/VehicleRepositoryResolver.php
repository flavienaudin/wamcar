<?php


namespace AppBundle\Services\Vehicle;


use AppBundle\Doctrine\Repository\DoctrinePersonalVehicleRepository;
use AppBundle\Doctrine\Repository\DoctrineProVehicleRepository;
use AppBundle\Session\Model\SessionMessage;
use Wamcar\User\BaseUser;
use Wamcar\User\ProUser;
use Wamcar\Vehicle\VehicleRepository;

class VehicleRepositoryResolver
{
    /** @var DoctrinePersonalVehicleRepository */
    protected $personalVehicleRepository;
    /** @var DoctrineProVehicleRepository */
    protected $proVehicleRepository;

    /**
     * VehicleRepositoryResolver constructor.
     * @param DoctrinePersonalVehicleRepository $personalVehicleRepository
     * @param DoctrineProVehicleRepository $proVehicleRepository
     */
    public function __construct(
        DoctrinePersonalVehicleRepository $personalVehicleRepository,
        DoctrineProVehicleRepository $proVehicleRepository
    )
    {
        $this->personalVehicleRepository = $personalVehicleRepository;
        $this->proVehicleRepository = $proVehicleRepository;
    }

    /**
     * @param BaseUser $user
     * @return VehicleRepository
     */
    public function getVehicleRepositoryByUser(BaseUser $user): VehicleRepository
    {
        if ($user instanceof ProUser) {
            return $this->proVehicleRepository;
        }

        return $this->personalVehicleRepository;
    }

    /**
     * @param SessionMessage $sessionMessage
     * @return VehicleRepository
     */
    public function getRepositoryBySessionMessage(SessionMessage $sessionMessage): VehicleRepository
    {
        return $sessionMessage->isProVehicle() ? $this->proVehicleRepository : $this->personalVehicleRepository;
    }

    /**
     * @param SessionMessage $sessionMessage
     * @return VehicleRepository
     */
    public function getRepositoryByHeaderSessionMessage(SessionMessage $sessionMessage): VehicleRepository
    {
        return $sessionMessage->isProVehicleHeader() ? $this->proVehicleRepository : $this->personalVehicleRepository;
    }
}
