<?php


namespace AppBundle\Security\Repository;


use AppBundle\Entity\ApplicationUser;

interface RegisteredWithConfirmationProvider
{
    /**
     * @param $registrationToken
     * @return ApplicationUser
     */
    public function findOneByRegistrationToken($registrationToken): ApplicationUser;
}
