<?php


namespace AppBundle\Entity;


interface AbleToLogin
{
    /**
     * @return mixed
     */
    public function getPasswordResetToken();

    /**
     * @param string $token
     * @return mixed
     */
    public function generatePasswordResetToken(string $token);

    /**
     * @param $password
     * @param $salt
     * @return mixed
     */
    public function resetPassword($password, $salt);

    /**
     * @return mixed
     */
    public function getRoles();
}
