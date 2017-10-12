<?php


namespace AppBundle\Security\Repository;


use AppBundle\Entity\ApplicationUser;

interface UserTokenable
{
    /**
     * @param $registrationToken
     * @return null|ApplicationUser
     */
    public function findOneByRegistrationToken($registrationToken): ApplicationUser;
}
