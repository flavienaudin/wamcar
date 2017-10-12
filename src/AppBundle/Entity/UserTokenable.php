<?php


namespace AppBundle\Entity;


interface UserTokenable
{
    /**
     * @param $registrationToken
     * @return null|ApplicationUser
     */
    public function findOneByRegistrationToken($registrationToken);
}
