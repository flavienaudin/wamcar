<?php

namespace Wamcar\User;

interface UserLikeVehicleRepository
{
    /**
     * @param int $likeId
     *
     * @return BaseLikeVehicle
     */
    public function findOne(int $likeId): BaseLikeVehicle;

    /**
     * @param array $criteria
     * @param array|null $orderBy
     * @return BaseLikeVehicle
     */
    public function findOneBy(array $criteria, array $orderBy = NULL);

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
     * @return array The entities.
     */
    public function findAll();

    /**
     * @param BaseLikeVehicle $like
     *
     * @return BaseLikeVehicle
     */
    public function add(BaseLikeVehicle $like);

    /**
     * @param BaseLikeVehicle $user
     *
     * @return BaseLikeVehicle
     */
    public function update(BaseLikeVehicle $user);

    /**
     * @param BaseLikeVehicle $like
     *
     * @return boolean
     */
    public function remove(BaseLikeVehicle $like);
}
