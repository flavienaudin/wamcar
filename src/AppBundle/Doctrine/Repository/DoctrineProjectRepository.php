<?php

namespace AppBundle\Doctrine\Repository;

use Doctrine\ORM\EntityRepository;
use Wamcar\User\Project;
use Wamcar\User\ProjectRepository;

class DoctrineProjectRepository extends EntityRepository implements ProjectRepository
{

    /**
     * {@inheritdoc}
     */
    public function update(Project $project): void
    {
        $this->_em->persist($project);
        $this->_em->flush();
    }

    /**
     * @param $ids array Array of entities'id
     * @return array
     */
    public function findByIds(array $ids): array
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('p')
            ->from($this->getClassName(), 'p')
            ->where($qb->expr()->in('p.id', $ids))
            ->orderBy($qb->expr()->asc('FIELD(p.id, :orderedIds ) '));
        $qb->setParameter('orderedIds', $ids);
        return $qb->getQuery()->getResult();

        /*$criteria = Criteria::create()
            ->where(Criteria::expr()->in("id", $ids));
        return $this->matching($criteria);*/
    }
}
