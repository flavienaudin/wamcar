<?php

namespace AppBundle\Services\Vehicle;

use AppBundle\Api\DTO\VehicleDTO as ApiVehicleDTO;
use AppBundle\Api\EntityBuilder\ProVehicleBuilder as ApiVehicleBuilder;
use AppBundle\Form\DTO\ProVehicleDTO as FormVehicleDTO;
use AppBundle\Form\EntityBuilder\ProVehicleBuilder as FormVehicleBuilder;
use SimpleBus\Message\Bus\MessageBus;
use Wamcar\Garage\Garage;
use Wamcar\Garage\GarageRepository;
use Wamcar\Vehicle\Event\ProVehicleCreated;
use Wamcar\Vehicle\Event\ProVehicleRemoved;
use Wamcar\Vehicle\ProVehicle;
use Wamcar\Vehicle\ProVehicleRepository;


class ProVehicleEditionService
{
    /** @var ProVehicleRepository */
    private $vehicleRepository;
    /** @var GarageRepository */
    private $garageRepository;
    /** @var array */
    private $vehicleBuilder;
    /** @var MessageBus */
    private $eventBus;


    /**
     * GarageEditionService constructor.
     * @param ProVehicleRepository $vehicleRepository
     * @param GarageRepository $garageRepository
     * @param MessageBus $eventBus
     */
    public function __construct(
        ProVehicleRepository $vehicleRepository,
        GarageRepository $garageRepository,
        MessageBus $eventBus
    )
    {
        $this->vehicleRepository = $vehicleRepository;
        $this->garageRepository = $garageRepository;
        $this->eventBus = $eventBus;
        $this->vehicleBuilder = [
            ApiVehicleDTO::class => ApiVehicleBuilder::class,
            FormVehicleDTO::class => FormVehicleBuilder::class
        ];
    }

    /**
     * @param CanBeProVehicle $proVehicleDTO
     * @param Garage $garage
     * @return ProVehicle
     */
    public function createInformations(CanBeProVehicle $proVehicleDTO, Garage $garage): ProVehicle
    {
        /** @var ProVehicle $proVehicle */
        $proVehicle = $this->vehicleBuilder[get_class($proVehicleDTO)]::newVehicleFromDTO($proVehicleDTO);
        $proVehicle->setGarage($garage);

        if (!$garage->isProVehicle($proVehicle)) {
            $garage->addProVehicle($proVehicle);
            $this->garageRepository->update($garage);
        }

        $this->vehicleRepository->add($proVehicle);
        $this->eventBus->handle(new ProVehicleCreated($proVehicle));

        return $proVehicle;
    }

    /**
     * @param CanBeProVehicle $proVehicleDTO
     * @param ProVehicle $vehicle
     * @return ProVehicle
     */
    public function updateInformations(CanBeProVehicle $proVehicleDTO, ProVehicle $vehicle): ProVehicle
    {
        /** @var ProVehicle $proVehicle */
        $proVehicle = $this->vehicleBuilder[get_class($proVehicleDTO)]::editVehicleFromDTO($proVehicleDTO, $vehicle);

        $this->vehicleRepository->update($proVehicle);
        return $vehicle;
    }

    /**
     * @param ProVehicle $proVehicle
     * @return ProVehicle
     */
    public function deleteVehicle(ProVehicle $proVehicle): ProVehicle
    {
        $this->vehicleRepository->remove($proVehicle);
        $this->eventBus->handle(new ProVehicleRemoved($proVehicle));
        return $proVehicle;
    }

    /**
     * @param $user
     * @param ProVehicle $vehicle
     * @return bool
     */
    public function canEdit($user, ProVehicle $vehicle): bool
    {
        return $vehicle !== null && $vehicle->canEditMe($user);
    }
}
