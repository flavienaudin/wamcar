<?php

namespace AppBundle\Doctrine\Repository;

use Wamcar\Vehicle\ProVehicleRepository;

class DoctrineProVehicleRepository extends DoctrineVehicleRepository implements ProVehicleRepository
{
    /**
     * @param $reference
     * @return null|void|\Wamcar\Vehicle\ProVehicle
     */
    public function findByReference($reference)
    {
        return $this->findOneBy(['reference' => $reference]);
    }

    /**
     * Return the $limit last vehicles
     * @param $limit
     * @return array
     */
    public function getLast($limit)
    {
        return $this->findBy(['deletedAt' => null], ['createdAt' => 'DESC'], $limit);
    }

}
