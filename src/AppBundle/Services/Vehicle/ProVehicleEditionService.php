<?php

namespace AppBundle\Services\Vehicle;

use AppBundle\Api\DTO\VehicleDTO;
use AppBundle\Form\DTO\ProVehicleDTO;
use AppBundle\Form\EntityBuilder\ProVehicleBuilder;
use Wamcar\Garage\Garage;
use Wamcar\Garage\GarageRepository;
use Wamcar\Vehicle\ProVehicle;
use Wamcar\Vehicle\VehicleRepository;


class ProVehicleEditionService
{
    /** @var VehicleRepository  */
    private $vehicleRepository;
    /** @var GarageRepository  */
    private $garageRepository;
    /** @var array  */
    private $vehicleBuilder;

    /**
     * GarageEditionService constructor.
     * @param VehicleRepository $vehicleRepository
     * @param GarageRepository $garageRepository
     */
    public function __construct(
        VehicleRepository $vehicleRepository,
        GarageRepository $garageRepository
    )
    {
        $this->vehicleRepository = $vehicleRepository;
        $this->garageRepository = $garageRepository;
        $this->vehicleBuilder = [
            VehicleDTO::class => \AppBundle\Api\EntityBuilder\ProVehicleBuilder::class,
            ProVehicleDTO::class => ProVehicleBuilder::class
        ];
    }

    /**
     * @param CanBeProVehicle $proVehicleDTO
     * @param Garage $garage
     * @return ProVehicle
     */
    public function editInformations(CanBeProVehicle $proVehicleDTO, Garage $garage): ProVehicle
    {
        /** @var ProVehicle $proVehicle */
        $proVehicle = $this->vehicleBuilder[get_class($proVehicleDTO)]::buildFromDTO($proVehicleDTO);
        $proVehicle->setGarage($garage);

        if (!$garage->isProVehicle($proVehicle)) {
            $garage->addProVehicle($proVehicle);
            $this->garageRepository->update($garage);
        }

        $this->vehicleRepository->add($proVehicle);
        return $proVehicle;
    }
}
