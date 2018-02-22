<?php


namespace AppBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Wamcar\Vehicle\BaseVehicle;

class VehicleRemoved
{
    public function preRemove(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        // only act on some "Product" entity
        if (!$entity instanceof BaseVehicle) {
            return;
        }


    }
}
