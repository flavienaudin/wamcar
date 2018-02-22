<?php

namespace AppBundle\Services\Vehicle;

use AppBundle\Api\DTO\VehicleDTO as ApiVehicleDTO;
use AppBundle\Api\EntityBuilder\ProVehicleBuilder as ApiVehicleBuilder;
use AppBundle\Doctrine\Entity\ProVehiclePicture;
use AppBundle\Doctrine\Repository\DoctrineMessageRepository;
use AppBundle\Form\DTO\ProVehicleDTO as FormVehicleDTO;
use AppBundle\Form\EntityBuilder\ProVehicleBuilder as FormVehicleBuilder;
use SimpleBus\Message\Bus\MessageBus;
use Wamcar\Garage\Garage;
use Wamcar\Garage\GarageRepository;
use Wamcar\Vehicle\BaseVehicle;
use Wamcar\Vehicle\Event\ProVehicleCreated;
use Wamcar\Vehicle\Event\ProVehicleRemoved;
use Wamcar\Vehicle\ProVehicle;
use Wamcar\Vehicle\ProVehicleRepository;


class VehicleEditionService
{
    /** @var DoctrineMessageRepository */
    private $messageRepository;


    /**
     * ProVehicleEditionService constructor.
     * @param DoctrineMessageRepository $messageRepository
     */
    public function __construct(
        DoctrineMessageRepository $messageRepository
    )
    {
        $this->messageRepository = $messageRepository;
    }

    public function deleteAssociationWithMessage(BaseVehicle $vehicle)
    {
        foreach ($vehicle->getMess as $getMess) {
            
        }
    }

}
