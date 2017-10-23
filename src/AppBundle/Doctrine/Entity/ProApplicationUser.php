<?php

namespace AppBundle\Doctrine\Entity;

use AppBundle\Security\ShouldConfirmRegistration;
use Wamcar\User\ProUser;

class ProApplicationUser extends ProUser implements \Serializable, ApplicationUser
{
    use ApplicationUserTrait;

    /**
     * ApplicationUser constructor.
     * @param string $email
     * @param string $password
     * @param string $salt
     */
    public function __construct(
        string $email,
        string $password,
        string $salt
    )
    {
        parent::__construct($email);

        $this->password = $password;
        $this->salt = $salt;
    }

    /**
     * @return bool
     */
    public function hasConfirmedRegistration(): bool
    {
        return true;
    }
}
