<?php

namespace AppBundle\Doctrine\Repository;

use Wamcar\Garage\Garage;
use Wamcar\Vehicle\ProVehicle;
use Wamcar\Vehicle\ProVehicleRepository;

class DoctrineProVehicleRepository extends DoctrineVehicleRepository implements ProVehicleRepository
{
    /**
     * @param $reference
     * @return ProVehicle|null
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
        return $this->findBy([], ['createdAt' => 'DESC'], $limit);
    }


    /**
     * {@inheritdoc}
     */
    public function findAllForGarage(Garage $garage, array $orderBy = null, bool $ignoreSoftDeleted = false): array
    {
        if ($ignoreSoftDeleted) {
            return $this->findIgnoreSoftDeletedBy(['garage' => $garage], $orderBy);
        } else {
            return $this->findBy(['garage' => $garage], $orderBy);
        }
    }


    /**
     * @inheritdoc
     */
    public function findByGarageAndExcludedReferences(Garage $garage, array $references)
    {

        $qb = $this->createQueryBuilder('v');
        $qb->where($qb->expr()->eq('v.garage', ':garage'))
            ->andwhere($qb->expr()->notIn('v.reference', $references))
            ->andWhere($qb->expr()->isNotNull('v.reference'));
        $qb->setParameter('garage', $garage);
        return $qb->getQuery()->execute();
    }
}

