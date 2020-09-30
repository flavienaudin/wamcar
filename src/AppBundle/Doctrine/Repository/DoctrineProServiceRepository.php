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
    public function findByNames(array  $proServiceNames, bool $orderByName = true){
        $qb = $this->createQueryBuilder('s');
        if(!empty($proServiceNames)){
            $qb->where($qb->expr()->in("s.name", $proServiceNames));
        }else{
            $qb->where($qb->expr()->eq("s.name", ":falseName"));
            $qb->setParameter('falseName', 'xFalseName');
        }
        if($orderByName) {
            $qb->orderBy('s.name', 'ASC');
        }else{
            $qb->orderBy($qb->expr()->asc('FIELD(s.name, :orderedNames) '));
            $qb->setParameter('orderedNames', $proServiceNames);
        }
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