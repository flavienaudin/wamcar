<?php


namespace AppBundle\Security\Repository;


use AppBundle\Doctrine\Entity\ApplicationUser;

interface RegisteredWithResettablePasswordProvider
{
    /**
     * @param $passwordResetToken
     * @return null|ApplicationUser
     */
    public function findOneByPasswordResetToken($passwordResetToken);

}
