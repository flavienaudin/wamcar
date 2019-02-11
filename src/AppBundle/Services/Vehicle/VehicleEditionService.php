<?php

namespace AppBundle\Services\Vehicle;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManager;
use Wamcar\Conversation\Message;
use Wamcar\Vehicle\BaseVehicle;
use Wamcar\Vehicle\PersonalVehicle;
use Wamcar\Vehicle\PersonalVehicleRepository;
use Wamcar\Vehicle\ProVehicle;
use Wamcar\Vehicle\ProVehicleRepository;


class VehicleEditionService
{

    /** @var EntityManager */
    private $em;
    /** @var ProVehicleRepository $proVehicleRepository*/
    private $proVehicleRepository;
    /** @var PersonalVehicleRepository $personalVehicleRepository */
    private $personalVehicleRepository;


    /**
     * VehicleEditionService constructor.
     * @param EntityManager $em
     * @param ProVehicleRepository $proVehicleRepository
     * @param PersonalVehicleRepository $personalVehicleRepository
     */
    public function __construct(EntityManager $em, ProVehicleRepository $proVehicleRepository, PersonalVehicleRepository $personalVehicleRepository)
    {
        $this->em = $em;
        $this->proVehicleRepository = $proVehicleRepository;
        $this->personalVehicleRepository = $personalVehicleRepository;
    }

    /**
     * {@inheritdoc
     */
    public function getLast(string $vehicleClass, $limit)
    {
        if($vehicleClass === ProVehicle::class) {
            return $this->proVehicleRepository->findBy([], ['createdAt' => Criteria::DESC], $limit);
        }elseif($vehicleClass === PersonalVehicle::class) {
            return $this->personalVehicleRepository->findBy([], ['createdAt' => Criteria::DESC], $limit);
        }
        return null;
    }

    /**
     * @param BaseVehicle $vehicle
     */
    public function deleteAssociationWithMessage(BaseVehicle $vehicle): void
    {
        /** @var Message $headerMessage */
        foreach ($vehicle->getHeaderMessages() as $headerMessage) {
            $headerMessage->removeVehicleHeader();
            $this->em->persist($headerMessage);
        }
        /** @var Message $message */
        foreach ($vehicle->getMessages() as $message) {
            $message->removeVehicle();
            $this->em->persist($message);
        }
    }
}
