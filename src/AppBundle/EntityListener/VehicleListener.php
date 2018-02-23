<?php


namespace AppBundle\EntityListener;

use AppBundle\Services\Vehicle\VehicleEditionService;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Wamcar\Vehicle\BaseVehicle;

class VehicleListener
{
    /**
     * @var VehicleEditionService $vehicleEditionService
     */
    protected $vehicleEditionService;

    public function __construct(VehicleEditionService $vehicleEditionService)
    {
        $this->vehicleEditionService = $vehicleEditionService;
    }

    public function preRemove(BaseVehicle $vehicle, LifecycleEventArgs $event)
    {
        $this->vehicleEditionService->deleteAssociationWithMessage($vehicle);
    }
}
