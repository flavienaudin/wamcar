<?php

namespace AppBundle\Services\Vehicle;

use AppBundle\Api\DTO\VehicleDTO as ApiVehicleDTO;
use AppBundle\Form\DTO\ProVehicleDTO as FormVehicleDTO;
use SimpleBus\Message\Bus\MessageBus;
use Wamcar\Garage\Garage;
use Wamcar\Garage\GarageRepository;
use Wamcar\Vehicle\Event\ProVehicleCreated;
use Wamcar\Vehicle\Event\VehicleCreated;
use Wamcar\Vehicle\ProVehicle;
use Wamcar\Vehicle\VehicleRepository;
use AppBundle\Api\EntityBuilder\ProVehicleBuilder as ApiVehicleBuilder;
use AppBundle\Form\EntityBuilder\ProVehicleBuilder as FormVehicleBuilder;


class ProVehicleEditionService
{
    /** @var VehicleRepository  */
    private $vehicleRepository;
    /** @var GarageRepository  */
    private $garageRepository;
    /** @var array  */
    private $vehicleBuilder;
    /** @var MessageBus */
    private $eventBus;


    /**
     * GarageEditionService constructor.
     * @param VehicleRepository $vehicleRepository
     * @param GarageRepository $garageRepository
     * @param MessageBus $eventBus
     */
    public function __construct(
        VehicleRepository $vehicleRepository,
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
     * @param ProVehicle|null $vehicle
     * @param Garage $garage
     */
    public function saveInformations(CanBeProVehicle $proVehicleDTO, ProVehicle $vehicle = null, Garage $garage)
    {
        if ($vehicle) {
            $this->updateInformations($proVehicleDTO, $vehicle);
        } else {
            $this->createInformations($proVehicleDTO, $garage);
        }
    }


    /**
     * @param CanBeProVehicle $proVehicleDTO
     * @param Garage $garage
     * @return ProVehicle
     */
    public function createInformations(CanBeProVehicle $proVehicleDTO, Garage $garage): ProVehicle
    {
        /** @var ProVehicle $proVehicle */
        $proVehicle = $this->vehicleBuilder[get_class($proVehicleDTO)]::buildFromDTO($proVehicleDTO);
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
     * @param FormVehicleDTO $proVehicleDTO
     * @param ProVehicle $vehicle
     * @return FormVehicleDTO
     */
    public function updateInformations(FormVehicleDTO $proVehicleDTO, ProVehicle $vehicle): FormVehicleDTO
    {
        /** @var ProVehicle $proVehicle */
        $proVehicle = FormVehicleBuilder::buildUpdateFromDTO($proVehicleDTO, $vehicle);

        $this->vehicleRepository->update($proVehicle);
        return $proVehicleDTO;
    }
}
