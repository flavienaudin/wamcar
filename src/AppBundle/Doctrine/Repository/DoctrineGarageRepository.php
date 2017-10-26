<?php

namespace AppBundle\Doctrine\Repository;

use Doctrine\ORM\EntityRepository;
use Wamcar\Garage\Garage;
use Wamcar\Garage\GarageRepository;

class DoctrineGarageRepository extends EntityRepository implements GarageRepository
{
    /**
     * {@inheritdoc}
     */
    public function findAll(): array
    {
        return $this->findBy([]);
    }

    /**
     * {@inheritdoc}
     */
    public function findOne(int $userId): Garage
    {
        return $this->findOneBy(['id' => $userId]);
    }

    /**
     * {@inheritdoc}
     */
    public function add(Garage $garage)
    {
        $this->_em->persist($garage);
        $this->_em->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function update(Garage $garage)
    {
        $garage = $this->_em->merge($garage);
        $this->_em->persist($garage);
        $this->_em->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function remove(Garage $garage)
    {
        $this->_em->remove($garage);
        $this->_em->flush();
    }


}
