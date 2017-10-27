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
    public function setPasswordResetToken(string $token)
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
}
