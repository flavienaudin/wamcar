<?php


namespace AppBundle\EventListener;

use AppBundle\Services\Vehicle\VehicleEditionService;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Wamcar\Vehicle\BaseVehicle;

class VehicleRemoved
{
    /**
     * @var VehicleEditionService $vehicleEditionService
     */
    protected $vehicleEditionService;

    public function __construct(VehicleEditionService $vehicleEditionService)
    {
        $this->vehicleEditionService = $vehicleEditionService;
    }

    public function preRemove(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        // only act on some "Product" entity
        if (!$entity instanceof BaseVehicle) {
            return;
        }

        $this->vehicleEditionService->deleteAssociationWithMessage($entity);

    }
}
