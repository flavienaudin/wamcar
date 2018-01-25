<?php

namespace AppBundle\Doctrine\Repository;

use AppBundle\Security\HasPasswordResettable;

trait PasswordResettableRepositoryTrait
{

    /**
     * {@inheritdoc}
     */
    public function findOneByPasswordResetToken($passwordResetToken) {
        return $this->findOneBy(['passwordResetToken' => $passwordResetToken]);
    }

    /**
     * {@inheritdoc}
     */
    public function updatePassword(HasPasswordResettable $user, string $password, string $salt): HasPasswordResettable
    {
        $user->resetPassword($password, $salt);
        $this->update($user);

        return $user;
    }
}
