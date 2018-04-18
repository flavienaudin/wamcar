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
    public function findOneByUserAndVehicle(BaseUser $user, BaseVehicle $vehicle): ?BaseVehicle{

        return $this->findOneBy(['user' => $user, 'vehicle' => $vehicle]);
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
