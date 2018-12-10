<?php

namespace TypeForm\Doctrine\Repository;

use Doctrine\ORM\EntityRepository;
use TypeForm\Doctrine\Entity\AffinityAnswer;
use Wamcar\User\PersonalUser;
use Wamcar\User\ProUser;

class DoctrineAffinityAnswerRepository extends EntityRepository
{
    /**
     * {@inheritdoc}
     */
    public function add(AffinityAnswer $affinityAnswer)
    {
        $this->_em->persist($affinityAnswer);
        $this->_em->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function update(AffinityAnswer $affinityAnswer)
    {
        $affinityAnswer = $this->_em->merge($affinityAnswer);
        $this->_em->persist($affinityAnswer);
        $this->_em->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function remove(AffinityAnswer $affinityAnswer)
    {
        $this->_em->remove($affinityAnswer);
        $this->_em->flush();
    }

    /**
     * Retrieve answers of Pro affinity form, that were NOT treated (not yet used to calculate user's affinity degrees
     *
     * @return mixed
     */
    public function retrieveUntreatedProAnswer()
    {
        $qb = $this->createQueryBuilder('a');
        $qb
            ->join('a.user', 'u')
            ->where($qb->expr()->isNull('a.treatedAt'))
            ->andWhere($qb->expr()->isInstanceOf('u', ProUser::class));
        return $qb->getQuery()->getResult();
    }

    /**
     * Retrieve answers of Pro affinity form, that were treated (used to calculate user's affinity degrees
     *
     * @return mixed
     */
    public function retrieveTreatedProAnswer()
    {
        $qb = $this->createQueryBuilder('a');
        $qb
            ->join('a.user', 'u')
            ->where($qb->expr()->isNotNull('a.treatedAt'))
            ->andWhere($qb->expr()->isInstanceOf('u', ProUser::class));
        return $qb->getQuery()->getResult();
    }

    /**
     * Retrieve answers of Personal affinity form, that were NOT treated (not yet used to calculate user's affinity degrees
     *
     * @return mixed
     */
    public function retrieveUntreatedPersonalAnswer()
    {
        $qb = $this->createQueryBuilder('a');
        $qb
            ->join('a.user', 'u')
            ->where($qb->expr()->isNull('a.treatedAt'))
            ->andWhere($qb->expr()->isInstanceOf('u', PersonalUser::class));
        return $qb->getQuery()->getResult();
    }

    /**
     * Retrieve answers of Personal affinity form, that were treated (used to calculate user's affinity degrees
     *
     * @return mixed
     */
    public function retrieveTreatedPersonalAnswer()
    {
        $qb = $this->createQueryBuilder('a');
        $qb
            ->join('a.user', 'u')
            ->where($qb->expr()->isNotNull('a.treatedAt'))
            ->andWhere($qb->expr()->isInstanceOf('u', PersonalUser::class));
        return $qb->getQuery()->getResult();
    }

}