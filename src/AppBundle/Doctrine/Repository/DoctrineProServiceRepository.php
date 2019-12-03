<?php


namespace AppBundle\Doctrine\Repository;


use Doctrine\ORM\EntityRepository;
use Wamcar\User\ProService;
use Wamcar\User\ProServiceRepository;

class DoctrineProServiceRepository extends EntityRepository implements ProServiceRepository
{

    /**
     * {@inheritdoc}
     */
    public function findByNames(array  $proServiceNames){
        $qb = $this->createQueryBuilder('s');
        if(!empty($proServiceNames)){
            $qb->where($qb->expr()->in("s.name", $proServiceNames));
        }else{
            $qb->where($qb->expr()->eq("s.name", ":falseName"));
            $qb->setParameter('falseName', 'xFalseName');
        }
        $qb->orderBy('s.name', 'ASC');
        return $qb->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function remove(ProService $proService): void
    {
        $this->_em->remove($proService);
        $this->_em->flush();
    }
}