<?php

namespace Wamcar\User;

use Wamcar\Vehicle\BaseVehicle;

interface UserLikeVehicleRepository
{
    /**
     * @param int $likeId
     *
     * @return BaseLikeVehicle
     */
    public function findOne(int $likeId): BaseLikeVehicle;

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

    /**
     * @param array $criteria
     * @param array|null $orderBy
     * @return BaseLikeVehicle
     */
    public function findOneBy(array $criteria, array $orderBy = NULL);

    /**
     * @param BaseVehicle $vehicle
     * @return BaseLikeVehicle[]
     */
    public function findAllByVehicle(BaseVehicle $vehicle): array;

}
