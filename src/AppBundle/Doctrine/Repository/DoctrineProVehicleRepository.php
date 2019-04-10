<?php

namespace AppBundle\Doctrine\Repository;

use Wamcar\Garage\Garage;
use Wamcar\User\ProUser;
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
     * Return the $limit last vehicles
     * @param $limit
     * @return array
     */
    public function getLastWithPicture($limit)
    {
        $qb = $this->createQueryBuilder('pv');
        $qb->where($qb->expr()->exists('SELECT 1 FROM AppBundle\Doctrine\Entity\ProVehiclePicture pic WHERE pic.vehicle = pv'))
            ->orderBy('pv.createdAt', 'DESC')
        ->setMaxResults($limit);
        return $qb->getQuery()->getResult();
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


    /**
     * @inheritdoc
     */
    public function findDeletedVehiclesByUserAndSaleStatus(ProUser $proUser, bool $getNullSaleStatus)
    {

        $qb = $this->createQueryBuilder('v');
        $qb->where($qb->expr()->eq('v.seller', ':seller'))
            ->andWhere($qb->expr()->isNotNull('v.deletedAt'));
        /*if ($getNullSaleStatus) {
            $qb->andWhere($qb->expr()->isNull('v.saleStatus'));
        } else {
            $qb->andWhere($qb->expr()->isNotNull('v.saleStatus'));
        }*/
        $qb->setParameter('seller', $proUser);

        if ($this->getEntityManager()->getFilters()->isEnabled('softDeleteable')) {
            $this->getEntityManager()->getFilters()->disable('softDeleteable');
        }
        $result = $qb->getQuery()->execute();
        $this->getEntityManager()->getFilters()->enable('softDeleteable');
        return $result;
    }

}

