<?php

namespace AppBundle\Doctrine\Repository;

use Doctrine\Common\Collections\Criteria;
use Wamcar\Garage\Garage;
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

    /**
     * @inheritDoc
     */
    public function getByGarage(Garage $garage, array $orderBy = [])
    {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('garage', $garage))
            ->orderBy($orderBy);
        return $this->matching($criteria);
    }
}

