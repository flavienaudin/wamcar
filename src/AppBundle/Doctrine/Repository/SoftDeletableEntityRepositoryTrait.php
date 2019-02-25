<?php

namespace AppBundle\Doctrine\Repository;


/**
 * Implements methods to disable then enable the softDeletable filter, when searching for entities.
 * Trait SoftDeletableEntityRepositoryTrait
 * @package AppBundle\Doctrine\Repository
 */
trait SoftDeletableEntityRepositoryTrait
{

    /**
     * IgnoreSoftDeleted version of Finds an entity by its primary key / identifier
     *
     * @param mixed $id The identifier.
     * @param int|null $lockMode One of the \Doctrine\DBAL\LockMode::* constants
     *                              or NULL if no specific lock mode should be used
     *                              during the search.
     * @param int|null $lockVersion The lock version.
     *
     * @return object|null The entity instance or NULL if the entity can not be found.
     */
    public function findIgnoreSoftDeleted($id, $lockMode = null, $lockVersion = null)
    {
        if ($this->getEntityManager()->getFilters()->isEnabled('softDeleteable')) {
            $this->getEntityManager()->getFilters()->disable('softDeleteable');
        }
        $entity = parent::find($id, $lockMode, $lockVersion);
        $this->getEntityManager()->getFilters()->enable('softDeleteable');
        return $entity;
    }

    /**
     * IgnoreSoftDeleted version of Finds all entities in the repository.
     *
     * @return array The entities.
     */
    public function findIgnoreSoftDeletedAll()
    {
        return $this->findIgnoreSoftDeletedBy([]);

    }

    /**
     * IgnoreSoftDeleted version of Finds entities by a set of criteria.
     *
     * @param array $criteria
     * @param array|null $orderBy
     * @param int|null $limit
     * @param int|null $offset
     *
     * @return array The objects.
     */
    public function findIgnoreSoftDeletedBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {

        if ($this->getEntityManager()->getFilters()->isEnabled('softDeleteable')) {
            $this->getEntityManager()->getFilters()->disable('softDeleteable');
        }
        $entities = parent::findBy($criteria, $orderBy, $limit, $offset);
        $this->getEntityManager()->getFilters()->enable('softDeleteable');
        return $entities;
    }

    /**
     * IgnoreSoftDeleted version of Finds a single entity by a set of criteria.
     *
     * @param array $criteria
     * @param array|null $orderBy
     *
     * @return object|null The entity instance or NULL if the entity can not be found.
     */
    public function findIgnoreSoftDeletedOneBy(array $criteria, array $orderBy = null)
    {
        if ($this->getEntityManager()->getFilters()->isEnabled('softDeleteable')) {
            $this->getEntityManager()->getFilters()->disable('softDeleteable');
        }
        $entity = parent::findOneBy($criteria, $orderBy);
        $this->getEntityManager()->getFilters()->enable('softDeleteable');
        return $entity;
    }
}