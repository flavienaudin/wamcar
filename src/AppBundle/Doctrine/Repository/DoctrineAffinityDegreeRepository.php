<?php

namespace AppBundle\Doctrine\Repository;

use AppBundle\Doctrine\Entity\AffinityDegree;
use Doctrine\ORM\EntityRepository;
use Wamcar\User\BaseUser;

class DoctrineAffinityDegreeRepository extends EntityRepository
{

    /**
     * {@inheritdoc}
     */
    public function add(AffinityDegree $affinityDegree)
    {
        $this->_em->persist($affinityDegree);
        $this->_em->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function update(AffinityDegree $affinityDegree)
    {
        $affinityDegree = $this->_em->merge($affinityDegree);
        $this->_em->persist($affinityDegree);
        $this->_em->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function remove(AffinityDegree $affinityDegree)
    {
        $this->_em->remove($affinityDegree);
        $this->_em->flush();
    }

    /**
     * @param BaseUser $mainUser
     * @param BaseUser $withUser
     * @return AffinityDegree|null
     */
    public function findOne(BaseUser $mainUser, BaseUser $withUser): ?AffinityDegree
    {
        return $this->findOneBy(['id' => ["mainUser" => $mainUser, "withUser" => $withUser]]);
    }
}