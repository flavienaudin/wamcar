<?php


namespace AppBundle\Security;


interface HasPasswordResettable
{
    /**
     * @return mixed
     */
    public function getPasswordResetToken();

    /**
     * @param string $token
     * @return mixed
     */
    public function setPasswordResetToken(string $token);

    /**
     * @param $password
     * @param $salt
     * @return mixed
     */
    public function resetPassword($password, $salt);

}
