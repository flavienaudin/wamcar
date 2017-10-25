<?php


namespace AppBundle\Security;


use AppBundle\Doctrine\Entity\ApplicationUser;

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
    public function generatePasswordResetToken(string $token);

    /**
     * @param $password
     * @param $salt
     * @return mixed
     */
    public function resetPassword($password, $salt);

    /**
     * @param PasswordEditData $passwordEditData
     * @return mixed
     */
    public function updatePassword(PasswordEditData $passwordEditData): ApplicationUser;

}
