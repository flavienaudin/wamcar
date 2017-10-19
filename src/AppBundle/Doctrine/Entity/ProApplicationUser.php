<?php

namespace AppBundle\Doctrine\Entity;

use AppBundle\Security\Repository\ShouldConfirmRegistration;
use Wamcar\User\ProUser;
use Wamcar\Vehicle\Vehicle;

class ProApplicationUser extends ProUser implements \Serializable, ShouldConfirmRegistration
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
