<?php


namespace AppBundle\Security\Repository;


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
