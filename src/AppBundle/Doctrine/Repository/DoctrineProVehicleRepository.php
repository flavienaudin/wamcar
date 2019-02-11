<?php

namespace AppBundle\Doctrine\Repository;

use Wamcar\Garage\Garage;
use Wamcar\Vehicle\ProVehicleRepository;

class DoctrineProVehicleRepository extends DoctrineVehicleRepository implements ProVehicleRepository
{

    /**
     * {@inheritdoc}
     */
    public function findByGarage(Garage $garage, array $orderBy = [], bool $ignoreSoftDeleted = false): array
    {
        if ($ignoreSoftDeleted) {
            return $this->findIgnoreSoftDeletedBy(['garage' => $garage]);
        } else {
            return $this->findBy(['garage' => $garage]);
        }
    }

    /**
     * @param $reference
     * @return null|void|\Wamcar\Vehicle\ProVehicle
     */
    public function findByReference($reference)
    {
        return $this->findOneBy(['reference' => $reference]);
    }
}

