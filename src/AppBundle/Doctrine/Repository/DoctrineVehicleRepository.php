<?php

namespace AppBundle\Doctrine\Repository;

use Doctrine\ORM\EntityRepository;
use Wamcar\Garage\Garage;
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

    /**
     * @param Vehicle $vehicle
     */
    public function update(Vehicle $vehicle): void
    {
        $this->_em->merge($vehicle);
        $this->_em->persist($vehicle);
        $this->_em->flush();
    }

    /**
     * @param Vehicle $vehicle
     */
    public function remove(Vehicle $vehicle): void
    {
        throw new \LogicException("Not implemented");
    }

    /**
     * @return array
     */
    public function findAllForGarage(Garage $garage): array
    {
        throw new \LogicException("Not implemented");
    }
}
