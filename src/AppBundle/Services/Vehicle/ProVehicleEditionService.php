<?php

namespace AppBundle\Services\Vehicle;

use AppBundle\Api\DTO\VehicleDTO;
use AppBundle\Form\DTO\ProVehicleDTO;
use Wamcar\Garage\Garage;
use Wamcar\Garage\GarageRepository;
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
            VehicleDTO::class => ApiVehicleBuilder::class,
            ProVehicleDTO::class => FormVehicleBuilder::class
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
