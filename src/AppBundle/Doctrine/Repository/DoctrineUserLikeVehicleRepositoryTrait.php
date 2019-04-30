<?php

namespace AppBundle\Doctrine\Repository;

use Wamcar\User\BaseLikeVehicle;
use Wamcar\User\BaseUser;
use Wamcar\Vehicle\BaseVehicle;

trait DoctrineUserLikeVehicleRepositoryTrait
{

    /**
     * {@inheritdoc}
     */
    public function findOne(int $likeId): BaseLikeVehicle
    {
        return $this->findOneBy(['id' => $likeId]);
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByUserAndVehicle(BaseUser $user, BaseVehicle $vehicle): ?BaseVehicle
    {

        return $this->findOneBy(['user' => $user, 'vehicle' => $vehicle]);
    }

    /**
     * {@inheritdoc}
     */
    public function getCountSentLikes(BaseUser $user, ?int $sinceDays = 30, ?\DateTimeInterface $referenceDate = null): int
    {

        if (empty($referenceDate)) {
            $referenceDate = new \DateTime();
        }
        $firstDate = clone $referenceDate;
        $firstDate->sub(new \DateInterval('P' . $sinceDays . 'D'));

        $qb = $this->createQueryBuilder('l');
        $qb->select('COUNT(l.id)')
            ->where('l.user = :user')
            ->andwhere($qb->expr()->gte('l.updatedAt', ':firstDate'))
            ->andWhere($qb->expr()->lte('l.updatedAt', ':referenceDate'))
            ->setParameter('user', $user)->setParameter('firstDate', $firstDate)
            ->setParameter('referenceDate', $referenceDate);
        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * {@inheritdoc}
     */
    public function add(BaseLikeVehicle $like)
    {
        $this->_em->persist($like);
        $this->_em->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function update(BaseLikeVehicle $like)
    {
        $like = $this->_em->merge($like);
        $this->_em->persist($like);
        $this->_em->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function remove(BaseLikeVehicle $like)
    {
        $this->_em->remove($like);
        $this->_em->flush();
    }


}
