<?php

namespace AppBundle\Entity;

use Wamcar\User\City;
use Wamcar\User\Title;

class RegisteredUser extends ApplicationUser
{
    /**
     * RegisteredUser constructor.
     *
     * @param string $email
     * @param Title $title
     * @param string $name
     * @param string $phone
     * @param string $password
     * @param string $salt
     * @param City $city
     * @param string $registrationIp
     * @param bool $newsletterOptin
     * @param string|null $registrationToken
     * @param array $roles
     */
    public function __construct(
        string $email,
        Title $title = null,
        string $name = null,
        string $phone = null,
        string $password,
        string $salt,
        City $city = null,
        string $registrationIp,
        bool $newsletterOptin = false,
        string $registrationToken = null,
        array  $roles = ['ROLE_USER']
    )
    {
        parent::__construct(
            $email,
            $title,
            $name,
            $phone,
            $password,
            $salt,
            $city,
            $registrationIp,
            $newsletterOptin,
            $registrationToken,
            $roles
        );
    }

}
