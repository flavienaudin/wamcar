<?php

namespace Wamcar\Garage;

interface GarageRepository
{
    /**
     * @param int $garageId
     *
     * @return Garage
     */
    public function findOne(int $garageId): ?Garage;

    /**
     * @return Garage[]
     */
    public function findAll(): array;

    /**
     * Finds entities by a set of criteria, ordered, event if softdeleted
     *
     * @param array      $criteria
     * @param array|null $orderBy
     * @return Garage[]
     */
    public function findByIgnoreSoftDeleted(array $criteria = [], array $orderBy = null);

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

    /**
     * @param array $criteria
     * @param array|null $orderBy
     * @return Garage
     */
    public function findOneBy(array $criteria, array $orderBy = NULL);
}
