<?php


namespace AppBundle\Security\Repository;


use AppBundle\Entity\ApplicationUser;

interface ShouldConfirmRegistration
{
    /**
     * @return mixed
     */
    public function confirmRegistration();

    /**
     * @return bool
     */
    public function hasConfirmedRegistration(): bool;

}
