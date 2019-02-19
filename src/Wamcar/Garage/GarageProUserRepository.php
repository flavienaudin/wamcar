<?php

namespace Wamcar\Garage;

interface GarageProUserRepository
{
    /**
     * @param int $garageId
     * @param int $proUserId
     *
     * @return GarageProUser
     */
    public function findOne(int $garageId, int $proUserId): ?GarageProUser;

    /**
     * @return GarageProUser[]
     */
    public function findAll(): array;

    /** {@inheritdoc} */
    public function add(GarageProUser $garage);

    /** {@inheritdoc} */
    public function update(GarageProUser $garage);

    /** {@inheritdoc} */
    public function remove(GarageProUser $garage);

    /**
     * @param array $criteria
     * @param array|null $orderBy
     * @return GarageProUser
     */
    public function findOneBy(array $criteria, array $orderBy = NULL);

}
