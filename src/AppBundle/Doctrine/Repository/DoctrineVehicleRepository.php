<?php

namespace AppBundle\Doctrine\Repository;

use Doctrine\ORM\EntityRepository;
use Wamcar\Garage\Garage;
use Wamcar\Vehicle\Vehicle;

class DoctrineVehicleRepository extends EntityRepository
{
    /**
     * {@inheritdoc}
     */
    public function add(Vehicle $vehicle): void
    {
        $this->_em->persist($vehicle);
        $this->_em->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function update(Vehicle $vehicle): void
    {
        $this->_em->merge($vehicle);
        $this->_em->persist($vehicle);
        $this->_em->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function remove(Vehicle $vehicle): void
    {
        $this->_em->persist($vehicle);
        $this->_em->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function findAllForGarage(Garage $garage): array
    {
        throw new \LogicException("Not implemented");
    }
}
