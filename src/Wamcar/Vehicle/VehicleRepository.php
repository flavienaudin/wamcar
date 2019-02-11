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
     * Finds entities by a set of criteria.
     *
     * @param array      $criteria
     * @param array|null $orderBy
     * @param int|null   $limit
     * @param int|null   $offset
     *
     * @return array The objects.
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null);

    /**
     * Get {Pro|Personal} Vehicle by IDs, keeping the $ids order
     * @param $ids array Array of entities'id
     * @return array
     */
    public function findByIds(array $ids): array;
}
