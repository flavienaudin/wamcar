<?php

namespace AppBundle\Doctrine\Repository;

use Doctrine\ORM\NonUniqueResultException;
use Wamcar\Garage\Garage;
use Wamcar\Garage\GarageProUser;
use Wamcar\User\ProUser;
use Wamcar\Vehicle\ProVehicle;
use Wamcar\Vehicle\ProVehicleRepository;

class DoctrineProVehicleRepository extends DoctrineVehicleRepository implements ProVehicleRepository
{
    /**
     * @param $reference
     * @return object|ProVehicle|null
     */
    public function findByReference($reference)
    {
        return $this->findOneBy(['reference' => $reference]);
    }

    /**
     * @param $vin
     * @return mixed|void
     */
    public function findOneByVIN($vin)
    {
        return $this->findOneBy(['vin' => $vin]);
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
        $qb->where($qb->expr()->eq('v.garage', ':garage'));
        if (!empty($references)) {
            $qb->andwhere($qb->expr()->notIn('v.reference', $references));
        }
        $qb->andWhere($qb->expr()->isNotNull('v.reference'));
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

        // Garages of the ProUser
        $garages = [];
        /** @var GarageProUser $garageMembership */
        foreach ($proUser->getEnabledGarageMemberships() as $garageMembership) {
            $garages[] = $garageMembership->getGarage();
        }
        // For last two months
        $firstDate = new \DateTime();
        $firstDate->sub(new \DateInterval('P60D'));
        $lastDate = new \DateTime();

        // Query count total filtered results
        $qb = $this->createQueryBuilder('v');
        $qb->select('COUNT(v.id)')
            ->where($qb->expr()->in('v.garage', ':garages'))
            ->andWhere($qb->expr()->gte('v.deletedAt', ':firstDate'))
            ->andWhere($qb->expr()->lte('v.deletedAt', ':lastDate'))
            ->setParameter('garages', $garages)
            ->setParameter('firstDate', $firstDate)
            ->setParameter('lastDate', $lastDate);
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
        $qb->where($qb->expr()->in('v.garage', ':garages'))
            ->andWhere($qb->expr()->gte('v.deletedAt', ':firstDate'))
            ->andWhere($qb->expr()->lte('v.deletedAt', ':lastDate'))
            ->setParameter('garages', $garages)
            ->setParameter('firstDate', $firstDate)
            ->setParameter('lastDate', $lastDate)
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

        foreach ($params['order'] ?? [] as $order) {
            if ($order['column'] === "1") {
                $qb->addOrderBy('v.modelVersion.model.make.name', $order['dir'])
                    ->addOrderBy('v.modelVersion.model.name', $order['dir']);
            } else if ($order['column'] === "2") {
                $qb->addOrderBy('v.updatedAt', $order['dir']);
            } else if ($order['column'] === "3") {
                $qb->addOrderBy('v.saleDeclaration', $order['dir']);
            }
        }
        $result = $qb->getQuery()->execute();

        $this->getEntityManager()->getFilters()->enable('softDeleteable');

        return ['data' => $result, 'recordsTotalCount' => $totalCount, 'recordsFilteredCount' => $count];
    }

    /**
     * @inheritDoc
     */
    public function findDeletedProVehiclesByProUser(ProUser $proUser, ?int $sinceDays = 60, ?\DateTimeInterface $referenceDate = null): array
    {
        // Disable 'softDeletable' filter to get softDeleted vehicles
        if ($this->getEntityManager()->getFilters()->isEnabled('softDeleteable')) {
            $this->getEntityManager()->getFilters()->disable('softDeleteable');
        }

        // Date Interval for selection
        if (empty($referenceDate)) {
            $referenceDate = new \DateTime();
        }
        $firstDate = clone $referenceDate;
        $firstDate->sub(new \DateInterval('P' . $sinceDays . 'D'));

        // Garages of the ProUser
        $garages = [];
        /** @var GarageProUser $garageMembership */
        foreach ($proUser->getEnabledGarageMemberships() as $garageMembership) {
            $garages[] = $garageMembership->getGarage();
        }
        // Query filtered results
        $qb = $this->createQueryBuilder('v');
        $qb->where($qb->expr()->in('v.garage', ':garages'))
            ->andWhere($qb->expr()->gte('v.deletedAt', ':firstDate'))
            ->andWhere($qb->expr()->lte('v.deletedAt', ':lastDate'))
            ->andWhere($qb->expr()->isNull('v.saleDeclaration'))
            ->setParameter('garages', $garages)
            ->setParameter('firstDate', $firstDate)
            ->setParameter('lastDate', $referenceDate)
            ->indexBy('v', 'v.id');

        $result = $qb->getQuery()->execute();
        $this->getEntityManager()->getFilters()->enable('softDeleteable');
        return $result;
    }

    /**
     * @inheritDoc
     */
    public function findSoftDeletedForXMonth(?int $months = 3): array
    {
        $sinceDate = new \DateTime();
        $sinceDate->sub(new \DateInterval('P' . $months . 'M'));
        $qb = $this->createQueryBuilder('v');
        $qb->where($qb->expr()->lte('v.deletedAt', ':refDate'))
            ->setParameter('refDate', $sinceDate);

        // Disable 'softDeletable' filter to get softDeleted vehicles
        if ($this->getEntityManager()->getFilters()->isEnabled('softDeleteable')) {
            $this->getEntityManager()->getFilters()->disable('softDeleteable');
        }
        $result = $qb->getQuery()->execute();
        $this->getEntityManager()->getFilters()->enable('softDeleteable');
        return $result;
    }
}

