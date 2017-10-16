<?php

namespace Wamcar\User;

interface UserRepository
{
    /**
     * @param int $userId
     *
     * @return User
     */
    public function findOne(int $userId): User;

    /**
     * @return User[]
     */
    public function findAll(): array;

    /**
     * @param User $user
     *
     * @return User
     */
    public function add(User $user);

    /**
     * @param User $user
     *
     * @return User
     */
    public function update(User $user);

    /**
     * @param User $user
     *
     * @return boolean
     */
    public function remove(User $user);

    /**
     * @param array $criteria
     * @param array|null $orderBy
     * @return User
     */
    public function findOneBy(array $criteria, array $orderBy = NULL);

    /**
     * @param string $email
     * @return User
     */
    public function findOneByEmail(string $email);

}
