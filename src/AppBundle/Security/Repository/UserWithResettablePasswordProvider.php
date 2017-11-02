<?php


namespace AppBundle\Security\Repository;


use AppBundle\Security\HasPasswordResettable;

interface UserWithResettablePasswordProvider
{
    /**
     * @param $passwordResetToken
     * @return null|HasPasswordResettable
     */
    public function findOneByPasswordResetToken($passwordResetToken);

    /**
     * @param HasPasswordResettable $user
     * @param string $password
     * @param string $salt
     * @return HasPasswordResettable
     */
    public function updatePassword(HasPasswordResettable $user, string $password, string $salt): HasPasswordResettable;
}
