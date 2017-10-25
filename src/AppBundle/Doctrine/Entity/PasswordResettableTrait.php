<?php


namespace AppBundle\Doctrine\Entity;


use AppBundle\Security\HasPasswordResettable;

trait PasswordResettableTrait
{
    /** @var  string */
    protected $passwordResetToken;

    /**
     * @return string
     */
    public function getPasswordResetToken(): string
    {
        return $this->passwordResetToken;
    }

    /**
     *
     * @param string
     *
     * @return $this
     */
    public function generatePasswordResetToken(string $token)
    {
        $this->passwordResetToken = $token;

        return $this;
    }

    /**
     * @param $password
     * @param $salt
     */
    public function resetPassword($password, $salt)
    {
        $this->password = $password;
        $this->salt = $salt;
        // password has been reset, we need to reset the passwordResetToken
        $this->passwordResetToken = null;
    }

    /**
     * {@inheritdoc}
     */
    public function updatePassword(PasswordEditData $passwordEditData): HasPasswordResettable
    {
        /**
         * @var HasPasswordResettable $user
         * Retrieve user
         */
        $user = $this->findOne($passwordEditData->id);
        // update password and salt
        $user->resetPassword($passwordEditData->encodedPassword, $passwordEditData->salt);
        //and save modification
        $this->update($user);

        return $user;
    }
}
