<?php

namespace AppBundle\Doctrine\Repository;

use Wamcar\Vehicle\PersonalVehicleRepository;

class DoctrinePersonalVehicleRepository extends DoctrineVehicleRepository implements PersonalVehicleRepository
{

    /**
     * {@inheritdoc}
     */
    public function retrieveVehiclesWithLessThan2PicturesSince24h()
    {
        $qb = $this->createQueryBuilder('pv');
        $qb
            ->leftJoin('pv.pictures', 'vp')
            ->where($qb->expr()->andX(
                $qb->expr()->gte('pv.createdAt', ':select_interval_start'),
                $qb->expr()->lt('pv.createdAt', ':select_interval_end')
            ))
            ->groupBy('pv.id')
            ->having('count(vp.id) < 2');

        $selectIntervalStart = new \DateTime("now");
        $selectIntervalStart->sub(new \DateInterval('PT25H'));
        $qb->setParameter("select_interval_start", $selectIntervalStart);

        $selectIntervalEnd = new \DateTime("now");
        $selectIntervalEnd->sub(new \DateInterval('PT24H'));
        $qb->setParameter("select_interval_end", $selectIntervalEnd);

        return $qb->getQuery()->getResult();
    }
}
