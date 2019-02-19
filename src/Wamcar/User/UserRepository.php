<?php

namespace Wamcar\User;

interface UserRepository
{
    /**
     * @param int $userId
     *
     * @return BaseUser
     */
    public function findOne(int $userId): ?BaseUser;


    /**
     * @param array $criteria
     * @param array|null $orderBy
     * @return BaseUser
     */
    public function findOneBy(array $criteria, array $orderBy = NULL);

    /**
     * @param string $email
     * @return BaseUser
     */
    public function findOneByEmail(string $email);

    /**
     * @return BaseUser[]
     */
    public function findAll();

    /**
     * @param $ids array Array of entities'id
     * @return array
     */
    public function findByIds(array $ids): array;

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
     * @param BaseUser $user
     *
     * @return BaseUser
     */
    public function add(BaseUser $user);

    /**
     * @param BaseUser $user
     *
     * @return BaseUser
     */
    public function update(BaseUser $user);

    /**
     * @param BaseUser $user
     *
     * @return boolean
     */
    public function remove(BaseUser $user);
}
