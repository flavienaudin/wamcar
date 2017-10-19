<?php


namespace AppBundle\Security;


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
