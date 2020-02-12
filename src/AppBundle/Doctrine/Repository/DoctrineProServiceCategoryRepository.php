<?php


namespace AppBundle\Doctrine\Repository;


use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityRepository;
use Wamcar\User\ProServiceCategory;
use Wamcar\User\ProServiceCategoryRepository;

class DoctrineProServiceCategoryRepository extends EntityRepository implements ProServiceCategoryRepository
{

    /**
     * @inheritDoc
     */
    public function findEnabledOrdered()
    {
        $qb = $this->createQueryBuilder('c');

        $qb
            ->innerJoin('c.proServices', 's')
            ->where($qb->expr()->orX(
                $qb->expr()->isNotNull('c.positionMainFilter'),
                $qb->expr()->isNotNull('c.positionMoreFilter')
            ));
            /*->groupBy('c')
            ->having($qb->expr()->gt($qb->expr()->count('c.proServices'), 0));*/
        $qb->orderBy('c.positionMainFilter', Criteria::ASC)
            ->orderBy('c.positionMoreFilter', Criteria::ASC);

        return $qb->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function remove(ProServiceCategory $proServiceCategory): void
    {
        $this->_em->remove($proServiceCategory);
        $this->_em->flush();
    }
}