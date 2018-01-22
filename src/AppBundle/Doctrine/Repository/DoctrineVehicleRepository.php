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
        $this->_em->remove($vehicle);
        $this->_em->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function findAllForGarage(Garage $garage): array
    {
        return $this->findBy(['garage' => $garage]);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteAllForGarage(Garage $garage): void
    {
        $vehicleList = $this->findBy(['garage' => $garage]);
        foreach ($vehicleList as $vehicle) {
            $this->_em->remove($vehicle);
        }
        $this->_em->flush();
    }
}
