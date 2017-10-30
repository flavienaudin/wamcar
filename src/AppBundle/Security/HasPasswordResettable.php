<?php


namespace AppBundle\Security;


interface HasPasswordResettable
{
    /**
     * @return mixed
     */
    public function getPasswordResetToken();

    /**
     * @return mixed
     */
    public function generatePasswordResetToken();

    /**
     * @param $password
     * @param $salt
     * @return mixed
     */
    public function resetPassword($password, $salt);

}
