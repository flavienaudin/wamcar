<?php

namespace AppBundle\Doctrine\Entity;

use AppBundle\Security\HasPasswordResettable;
use AppBundle\Security\SecurityInterface\HasApiCredential;
use AppBundle\Security\SecurityTrait\ApiCredentialTrait;
use AppBundle\Services\User\CanBeGarageMember;
use Symfony\Component\Security\Core\User\UserInterface;
use Wamcar\Garage\Garage;
use Wamcar\Garage\GarageProUser;
use Wamcar\User\ProUser;

class ProApplicationUser extends ProUser implements \Serializable, ApplicationUser, UserInterface, HasPasswordResettable, CanBeGarageMember, HasApiCredential
{
    use ApplicationUserTrait;
    use PasswordResettableTrait;
    use ApiCredentialTrait;

    /** @var  string */
    protected $role;

    /**
     * ApplicationUser constructor.
     * @param string $email
     * @param string $password
     * @param string $salt
     * @param string $role
     */
    public function __construct(
        string $email,
        string $password,
        string $salt,
        string $role = null
    )
    {
        parent::__construct($email);

        $this->password = $password;
        $this->salt = $salt;
        $this->role = $role ?? 'ROLE_PRO';
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
        return [$this->role];
    }

    /**
     * @param Garage $garage
     * @return null|GarageProUser
     */
    public function getMembershipByGarage(Garage $garage): ?GarageProUser
    {
        /** @var GarageProUser $member */
        foreach ($garage->getMembers() as $member) {
            if ($member->getProUser() === $this) {
                return $member;
            }
        }

        return null;
    }

    /**
     * @param Garage $garage
     * @return bool
     */
    public function isMembershipOfGarage(Garage $garage): bool
    {
        /** @var GarageProUser $member */
        foreach ($garage->getMembers() as $member) {
            if ($member->getProUser() === $this) {
                return true;
            }
        }

        return false;
    }
}
