<?php


namespace AppBundle\Doctrine\Repository;


use AppBundle\Entity\ApplicationUser;

interface RegistrationTokenable
{
    /**
     * @param $registrationToken
     * @return null|ApplicationUser
     */
    public function findOneByRegistrationToken($registrationToken);
}
