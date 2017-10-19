<?php

namespace AppBundle\Doctrine\Entity;

use AppBundle\Security\ShouldConfirmRegistration;
use Wamcar\User\ProUser;

class ProApplicationUser extends ProUser implements \Serializable, ShouldConfirmRegistration, ApplicationUser
{
    use ApplicationUserTrait;

    /**
     * ApplicationUser constructor.
     * @param string $email
     * @param string $password
     * @param string $salt
     * @param string $registrationToken
     */
    public function __construct(
        string $email,
        string $password,
        string $salt,
        string $registrationToken
    )
    {
        parent::__construct($email);

        $this->password = $password;
        $this->salt = $salt;
        $this->registrationToken = $registrationToken;
    }
}
