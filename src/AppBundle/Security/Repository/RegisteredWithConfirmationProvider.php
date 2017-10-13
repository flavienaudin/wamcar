<?php


namespace AppBundle\Security\Repository;


use AppBundle\Entity\ApplicationUser;

interface RegisteredWithConfirmationProvider
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
