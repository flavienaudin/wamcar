<?php

namespace Wamcar\Garage;

use AppBundle\Security\SecurityInterface\ApiUserProvider;

interface GarageRepository extends ApiUserProvider
{
    /**
     * @param int $garageId
     *
     * @return Garage
     */
    public function findOne(int $garageId): ?Garage;

    /**
     * @param array $criteria
     * @param array|null $orderBy
     * @return Garage
     */
    public function findOneBy(array $criteria, array $orderBy = NULL);

    /**
     * @return Garage[]
     */
    public function findAll();

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
    public function findIgnoreSoftDeleted($id, $lockMode = null, $lockVersion = null);

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
    public function findIgnoreSoftDeletedBy(array $criteria, array $orderBy = null, $limit = null, $offset = null);

    /**
     * IgnoreSoftDeleted version of Finds a single entity by a set of criteria.
     *
     * @param array $criteria
     * @param array|null $orderBy
     *
     * @return object|null The entity instance or NULL if the entity can not be found.
     */
    public function findIgnoreSoftDeletedOneBy(array $criteria, array $orderBy = null);

    /**
     * @param Garage $garage
     *
     * @return Garage
     */
    public function add(Garage $garage);

    /**
     * @param Garage $garage
     *
     * @return Garage
     */
    public function update(Garage $garage): Garage;

    /**
     * @param Garage $garage
     *
     * @return boolean
     */
    public function remove(Garage $garage);
}
