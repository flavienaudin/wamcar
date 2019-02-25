<?php


namespace AppBundle\Doctrine\Entity;


use AppBundle\Security\HasPasswordResettable;
use AppBundle\Utils\TokenGenerator;

trait PasswordResettableTrait
{
    /** @var  string */
    protected $passwordResetToken;

    /**
     * @return null|string
     */
    public function getPasswordResetToken(): ?string
    {
        return $this->passwordResetToken;
    }

    /**
     *
     * @param string
     *
     * @return $this
     */
    public function generatePasswordResetToken()
    {
        $token = TokenGenerator::generateToken();
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
