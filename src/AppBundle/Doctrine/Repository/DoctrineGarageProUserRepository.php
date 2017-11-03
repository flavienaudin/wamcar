<?php

namespace AppBundle\Doctrine\Repository;

use Doctrine\ORM\EntityRepository;
use Wamcar\Garage\GarageProUser;
use Wamcar\Garage\GarageProUserRepository;

class DoctrineGarageProUserRepository extends EntityRepository implements GarageProUserRepository
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
    public function findOne(int $garageProUserId): GarageProUser
    {
        return $this->findOneBy(['id' => $garageProUserId]);
    }

    /**
     * {@inheritdoc}
     */
    public function add(GarageProUser $garageProUser)
    {
        $this->_em->persist($garageProUser);
        $this->_em->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function update(GarageProUser $garageProUser)
    {
        $garageProUser = $this->_em->merge($garageProUser);
        $this->_em->persist($garageProUser);
        $this->_em->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function remove(GarageProUser $garageProUser)
    {
        $this->_em->remove($garageProUser);
        $this->_em->flush();
    }
}
