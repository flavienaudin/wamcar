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
