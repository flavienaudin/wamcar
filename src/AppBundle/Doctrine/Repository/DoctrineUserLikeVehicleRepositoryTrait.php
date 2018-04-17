<?php

namespace AppBundle\Doctrine\Repository;

use Wamcar\User\BaseLikeVehicle;
use Wamcar\Vehicle\BaseVehicle;

trait DoctrineUserLikeVehicleRepositoryTrait
{
    /**
     * @param BaseVehicle $vehicle
     * @return BaseLikeVehicle[]
     */
    public function findAllByVehicle(BaseVehicle $vehicle): array
    {
        return $this->findBy(['vehicle' => $vehicle]);
    }

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
