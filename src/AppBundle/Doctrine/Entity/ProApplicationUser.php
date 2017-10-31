<?php

namespace AppBundle\Doctrine\Entity;

use AppBundle\Security\HasPasswordResettable;
use AppBundle\Security\SecurityInterface\HasApiCredential;
use AppBundle\Security\SecurityTrait\ApiCredentialTrait;
use Symfony\Component\Security\Core\User\UserInterface;
use Wamcar\User\ProUser;

class ProApplicationUser extends ProUser implements \Serializable, ApplicationUser, UserInterface, HasPasswordResettable, HasApiCredential
{
    use ApplicationUserTrait;
    use PasswordResettableTrait;
    use ApiCredentialTrait;

    /** @var  array */
    protected $roles;

    /**
     * ApplicationUser constructor.
     * @param string $email
     * @param string $password
     * @param string $salt
     * @param string $roles
     */
    public function __construct(
        string $email,
        string $password,
        string $salt,
        string $roles = null
    )
    {
        parent::__construct($email);

        $this->password = $password;
        $this->salt = $salt;
        $this->roles = $roles ? [$roles] : ['ROLE_PRO'];
        $this->generateApiCredentials();
    }

    /**
     * @return bool
     */
    public function hasConfirmedRegistration(): bool
    {
        return true;
    }

    /**
     * @return array
     */
    public function getRoles(): array
    {
        return $this->roles;
    }
}
