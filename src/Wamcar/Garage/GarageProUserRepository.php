<?php

namespace Wamcar\Garage;

interface GarageProUserRepository
{
    /**
     * @param int $garageId
     *
     * @return GarageProUser
     */
    public function findOne(int $garageId): ?GarageProUser;

    /**
     * @return GarageProUser[]
     */
    public function findAll(): array;

    /**
     * @param GarageProUser $garage
     *
     * @return GarageProUser
     */
    public function add(GarageProUser $garage);

    /**
     * @param GarageProUser $garage
     *
     * @return GarageProUser
     */
    public function update(GarageProUser $garage);

    /**
     * @param GarageProUser $garage
     *
     * @return boolean
     */
    public function remove(GarageProUser $garage);

    /**
     * @param array $criteria
     * @param array|null $orderBy
     * @return GarageProUser
     */
    public function findOneBy(array $criteria, array $orderBy = NULL);

}
