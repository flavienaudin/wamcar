<?php

namespace AppBundle\Doctrine\Repository;

use Doctrine\ORM\EntityRepository;
use Wamcar\Vehicle\Vehicle;
use Wamcar\Vehicle\VehicleRepository;

class DoctrineVehicleRepository extends EntityRepository implements VehicleRepository
{
    /**
     * @param Vehicle $vehicle
     */
    public function add(Vehicle $vehicle): void
    {
        $this->_em->persist($vehicle);
        $this->_em->flush();
    }
}
