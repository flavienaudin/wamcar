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
     * Finds entities by a set of criteria.
     *
     * @param array $criteria
     * @param array|null $orderBy
     * @param int|null $limit
     * @param int|null $offset
     *
     * @return array The objects.
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null);

    /**
     * @return array The entities.
     */
    public function findAll();

    /**
     * @param BaseUser $user L'utilisateur ayant liké
     * @param int|null $sinceDays Intervalle de temps avant la date de référence
     * @param \DateTimeInterface|null $referenceDate Date de référence (par défaut le jour même)
     * @return int
     */
    public function getCountSentLikes(BaseUser $user, ?int $sinceDays = 30, ?\DateTimeInterface $referenceDate = null): int;

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
