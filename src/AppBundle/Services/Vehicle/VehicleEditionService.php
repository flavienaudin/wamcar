<?php

namespace AppBundle\Services\Vehicle;

use Doctrine\ORM\EntityManager;
use Wamcar\Conversation\Message;
use Wamcar\Vehicle\BaseVehicle;


class VehicleEditionService
{
    /** @var EntityManager */
    private $em;


    /**
     * VehicleEditionService constructor.
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
