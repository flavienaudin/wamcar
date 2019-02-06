<?php

namespace AppBundle\Doctrine\Repository;


trait SluggableEntityRepositoryTrait
{

    /**
     * @param bool $onlyEmptySlug
     * @param bool $includeDeleted
     * @return array
     */
    public function findForSlugGeneration(bool $onlyEmptySlug = true, bool $includeDeleted = false): array
    {
        if ($includeDeleted) {
            $this->_em->getFilters()->disable('softDeleteable');
        }
        $qb = $this->createQueryBuilder('e');
        if ($onlyEmptySlug) {
            $qb->where($qb->expr()->orX(
                $qb->expr()->isNull('e.slug'),
                $qb->expr()->eq('e.slug', '?1')))
                ->setParameter(1, '');
        }
        $results = $qb->getQuery()->getResult();
        if ($includeDeleted) {
            $this->_em->getFilters()->enable('softDeleteable');
        }
        return $results;
    }
}