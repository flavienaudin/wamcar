<?php

namespace Wamcar\Vehicle;

interface VehicleRepository
{
    /**
     * @param Vehicle $vehicle
     */
    public function add(Vehicle $vehicle): void;

    /**
     * @param Vehicle $vehicle
     */
    public function update(Vehicle $vehicle): void;

    /**
     * @param Vehicle $vehicle
     */
    public function remove(Vehicle $vehicle): void;

    /**
     * @param string $id
     * @return Vehicle
     */
    public function find($id);

    /**
    * Finds all entities in the repository.
    *
    * @return array
    */
    public function findAll();

    /**
     * Get ProVehicle by IDs, keeping the $ids order
     * @param $ids array Array of entities'id
     * @return array
     */
    public function findByIds(array $ids): array;

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

}
