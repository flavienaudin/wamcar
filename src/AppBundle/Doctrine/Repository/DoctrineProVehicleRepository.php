<?php

namespace AppBundle\Doctrine\Repository;

use Doctrine\ORM\NonUniqueResultException;
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
    public function getDeletedProVehiclesByRequest(ProUser $proUser, array $params)
    {
        // Disable 'softDeletable' filter to get softDeleted vehicles
        if ($this->getEntityManager()->getFilters()->isEnabled('softDeleteable')) {
            $this->getEntityManager()->getFilters()->disable('softDeleteable');
        }

        // Query count total filtered results
        $qb = $this->createQueryBuilder('v');
        $qb->select('COUNT(v.id)')
            ->where($qb->expr()->eq('v.seller', ':seller'))
            ->andWhere($qb->expr()->isNotNull('v.deletedAt'))
            ->setParameter('seller', $proUser);
        try {
            $totalCount = $qb->getQuery()->getSingleScalarResult();
        } catch (NonUniqueResultException $e) {
            $totalCount = null;
        }

        if (isset($params['search']) && !empty($params['search']['value'])) {
            $qb->join('v.garage', 'g')
                ->andWhere(
                    $qb->expr()->orX(
                        $qb->expr()->like('v.modelVersion.model.name', ':searchValue'),
                        $qb->expr()->like('v.modelVersion.model.make.name', ':searchValue'),
                        $qb->expr()->like('g.name', ':searchValue')
                    )
                );
            $qb->setParameter('searchValue', '%' . $params['search']['value'] . '%');
        }

        try {
            $count = $qb->getQuery()->getSingleScalarResult();
        } catch (NonUniqueResultException $e) {
            $count = null;
        }

        // Query filtered results
        $qb = $this->createQueryBuilder('v');
        $qb->where($qb->expr()->eq('v.seller', ':seller'))
            ->andWhere($qb->expr()->isNotNull('v.deletedAt'))
            ->setParameter('seller', $proUser)
            ->setFirstResult($params['start'])
            ->setMaxResults($params['length']);

        if (isset($params['search']) && !empty($params['search']['value'])) {
            $qb->join('v.garage', 'g')
                ->andWhere(
                    $qb->expr()->orX(
                        $qb->expr()->like('v.modelVersion.model.name', ':searchValue'),
                        $qb->expr()->like('v.modelVersion.model.make.name', ':searchValue'),
                        $qb->expr()->like('g.name', ':searchValue')
                    )
                );
            $qb->setParameter('searchValue', '%' . $params['search']['value'] . '%');
        }

        foreach ($params['order'] as $order) {
            if ($order['column'] === "1") {
                $qb->addOrderBy('v.modelVersion.model.make.name', $order['dir'])
                    ->addOrderBy('v.modelVersion.model.name', $order['dir']);
            } else if ($order['column'] === "2") {
                $qb->addOrderBy('v.updatedAt', $order['dir']);
            }
        }
        $result = $qb->getQuery()->execute();

        $this->getEntityManager()->getFilters()->enable('softDeleteable');

        return ['data' => $result, 'recordsTotalCount' => $totalCount, 'recordsFilteredCount' => $count];
    }

}

