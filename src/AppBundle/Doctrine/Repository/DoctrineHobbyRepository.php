<?php


namespace AppBundle\Doctrine\Repository;


use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Wamcar\User\Hobby;
use Wamcar\User\HobbyRepository;

class DoctrineHobbyRepository extends EntityRepository implements HobbyRepository
{

    /**
     * {@inheritdoc}
     */
    public function remove(Hobby $hobby): void
    {
        $this->_em->remove($hobby);
        $this->_em->flush();
    }

    /**
     * Admin filter: order hobbies by name
     */
    public static function adminQueryBuilderToOrderHobby(EntityRepository $r): QueryBuilder
    {
        $qb = $r->createQueryBuilder('h');
        return $qb->orderBy('h.name');
    }
}