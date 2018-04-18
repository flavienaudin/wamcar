<?php

namespace Wamcar\User;

interface UserRepository
{
    /**
     * @param int $userId
     *
     * @return BaseUser
     */
    public function findOne(int $userId): BaseUser;

    /**
     * @return BaseUser[]
     */
    public function findAll();

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

}
