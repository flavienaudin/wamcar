<?php

namespace AppBundle\Services\Vehicle;

use AppBundle\Api\DTO\VehicleDTO as ApiVehicleDTO;
use AppBundle\Api\EntityBuilder\ProVehicleBuilder as ApiVehicleBuilder;
use AppBundle\Doctrine\Entity\ProVehiclePicture;
use AppBundle\Doctrine\Repository\DoctrineMessageRepository;
use AppBundle\Form\DTO\ProVehicleDTO as FormVehicleDTO;
use AppBundle\Form\EntityBuilder\ProVehicleBuilder as FormVehicleBuilder;
use Doctrine\ORM\EntityManager;
use SimpleBus\Message\Bus\MessageBus;
use Wamcar\Conversation\Message;
use Wamcar\Garage\Garage;
use Wamcar\Garage\GarageRepository;
use Wamcar\Vehicle\BaseVehicle;
use Wamcar\Vehicle\Event\ProVehicleCreated;
use Wamcar\Vehicle\Event\ProVehicleRemoved;
use Wamcar\Vehicle\ProVehicle;
use Wamcar\Vehicle\ProVehicleRepository;


class VehicleEditionService
{
    /** @var EntityManager */
    private $em;


    /**
     * ProVehicleEditionService constructor.
     * @param EntityManager $em
     */
    public function __construct(
        EntityManager $em
    )
    {
        $this->em = $em;
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
