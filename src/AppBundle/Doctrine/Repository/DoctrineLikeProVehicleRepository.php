<?php

namespace AppBundle\Doctrine\Repository;


use Doctrine\ORM\EntityRepository;
use Wamcar\Garage\GarageProUser;
use Wamcar\User\BaseUser;
use Wamcar\User\ProLikeVehicle;
use Wamcar\User\ProUser;
use Wamcar\User\UserLikeVehicleRepository;
use Wamcar\Vehicle\ProVehicle;

class DoctrineLikeProVehicleRepository extends EntityRepository implements UserLikeVehicleRepository
{
    use SoftDeletableEntityRepositoryTrait;
    use DoctrineUserLikeVehicleRepositoryTrait;


    /**
     * @param BaseUser $user
     * @param ProVehicle $vehicle
     * @return ProLikeVehicle|null
     */
    public function findOneByUserAndVehicle(BaseUser $user, ProVehicle $vehicle): ?ProLikeVehicle
    {
        return $this->findOneBy(['user' => $user, 'vehicle' => $vehicle]);
    }

    /**
     * @param ProVehicle $vehicle
     * @return ProLikeVehicle[]
     */
    public function findAllByVehicle(ProVehicle $vehicle): array
    {
        return $this->findBy(['vehicle' => $vehicle]);
    }

    /**
     * @inheritDoc
     */
    public function getCountReceivedLikes(ProUser $user, ?int $sinceDays = 30, ?\DateTimeInterface $referenceDate = null): int
    {
        if (empty($referenceDate)) {
            $referenceDate = new \DateTime();
        }
        $firstDate = clone $referenceDate;
        $firstDate->sub(new \DateInterval('P' . $sinceDays . 'D'));

        $userGaragesIds = $user->getEnabledGarageMemberships()->map(function (GarageProUser $garageProUser) {
                return $garageProUser->getGarage()->getId();
            })->toArray();

        $qb = $this->createQueryBuilder('l');
        $qb->select('COUNT(l.id)')
            ->join('l.vehicle', 'v')
            ->where($qb->expr()->in('v.garage', ':garagesIds'))
            ->andWhere($qb->expr()->isNull('v.deletedAt'))
            ->andwhere($qb->expr()->gte('l.updatedAt', ':firstDate'))
            ->andWhere($qb->expr()->lte('l.updatedAt', ':referenceDate'))
            ->setParameter('garagesIds', $userGaragesIds )
            ->setParameter('firstDate', $firstDate)
            ->setParameter('referenceDate', $referenceDate);
        return $qb->getQuery()->getSingleScalarResult();
    }
}