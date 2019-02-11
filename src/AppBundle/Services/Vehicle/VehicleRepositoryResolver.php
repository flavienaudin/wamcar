<?php


namespace AppBundle\Services\Vehicle;


use AppBundle\Doctrine\Repository\DoctrinePersonalVehicleRepository;
use AppBundle\Doctrine\Repository\DoctrineProVehicleRepository;
use AppBundle\Doctrine\Repository\DoctrineVehicleRepository;
use AppBundle\Session\Model\SessionMessage;
use Wamcar\User\BaseUser;
use Wamcar\User\ProUser;

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
     * @return DoctrineVehicleRepository
     */
    public function getVehicleRepositoryByUser(BaseUser $user): DoctrineVehicleRepository
    {
        if ($user instanceof ProUser) {
            return $this->proVehicleRepository;
        }

        return $this->personalVehicleRepository;
    }

    /**
     * @param SessionMessage $sessionMessage
     * @return DoctrineVehicleRepository
     */
    public function getRepositoryBySessionMessage(SessionMessage $sessionMessage): DoctrineVehicleRepository
    {
        return $sessionMessage->isProVehicle() ? $this->proVehicleRepository : $this->personalVehicleRepository;
    }

    /**
     * @param SessionMessage $sessionMessage
     * @return DoctrineVehicleRepository
     */
    public function getRepositoryByHeaderSessionMessage(SessionMessage $sessionMessage): DoctrineVehicleRepository
    {
        return $sessionMessage->isProVehicleHeader() ? $this->proVehicleRepository : $this->personalVehicleRepository;
    }
}
