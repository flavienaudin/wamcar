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
        return parent::findAll();
    }

    /**
     * {@inheritdoc}
     */
    public function findOne(int $garageId, int $proUserId): ?GarageProUser
    {
        return $this->findOneBy(['garage' => $garageId, 'proUser' => $proUserId]);
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
