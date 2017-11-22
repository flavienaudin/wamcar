<?php

namespace AppBundle\Services\Vehicle;

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
    }

    /**
     * @param ProVehicleDTO $proVehicleDTO
     * @param Garage $garage
     * @return ProVehicleDTO
     */
    public function editInformations(ProVehicleDTO $proVehicleDTO, Garage $garage): ProVehicleDTO
    {
        /** @var ProVehicle $proVehicle */
        $proVehicle = ProVehicleBuilder::buildFromDTO($proVehicleDTO);
        $proVehicle->setGarage($garage);

        if (!$garage->isProVehicle($proVehicle)) {
            $garage->addProVehicle($proVehicle);
            $this->garageRepository->update($garage);
        }

        $this->vehicleRepository->add($proVehicle);
        return $proVehicleDTO;
    }
}
