<?php

namespace AppBundle\Doctrine\Repository;


trait SluggableEntityRepositoryTrait
{

    /**
     * {@inheritdoc}
     */
    public function findOneBySlugIgnoreSoftDeleted(string $slug)
    {
        if($this->_em->getFilters()->isEnabled('softDeleteable')) {
            $this->_em->getFilters()->disable('softDeleteable');
        }
        $entity = self::findOneBy(['slug' => $slug]);
        $this->_em->getFilters()->enable('softDeleteable');
        return $entity;

    }

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