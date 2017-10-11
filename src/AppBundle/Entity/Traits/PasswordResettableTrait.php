<?php


namespace AppBundle\Entity\Traits;


trait PasswordResettableTrait
{
    /** @var string */
    protected $password;
    /** @var string */
    protected $salt;
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
}
