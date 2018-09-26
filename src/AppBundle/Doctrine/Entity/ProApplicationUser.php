<?php

namespace AppBundle\Doctrine\Entity;

use AppBundle\Security\HasPasswordResettable;
use AppBundle\Services\User\CanBeGarageMember;
use AppBundle\Services\User\CanBeInConversation;
use Mgilet\NotificationBundle\Annotation\Notifiable;
use Mgilet\NotificationBundle\NotifiableInterface;
use Wamcar\Garage\Garage;
use Wamcar\Garage\GarageProUser;
use Wamcar\Location\City;
use Wamcar\User\ProUser;

/**
 * Class ProApplicationUser
 * @package AppBundle\Doctrine\Entity
 * @Notifiable(name="ProApplicationUser")
 */
class ProApplicationUser extends ProUser implements \Serializable, ApplicationUser, HasPasswordResettable, CanBeGarageMember, CanBeInConversation, NotifiableInterface
{
    use ApplicationUserTrait;
    use PasswordResettableTrait;

    /** @var  string */
    protected $role;

    /**
     * ApplicationUser constructor.
     * @param string $email
     * @param string $password
     * @param string $salt
     * @param string $role
     * @param string $firstName
     * @param string|null $name
     * @param City|null $city
     */
    public function __construct(
        string $email,
        string $password,
        string $salt,
        string $firstName,
        string $name = null,
        string $role = null,
        City $city = null
    )
    {
        parent::__construct($email, $firstName, $name, $city);

        $this->password = $password;
        $this->salt = $salt;
        $this->role = $role ?? 'ROLE_PRO';
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
    public function isMemberOfGarage(Garage $garage): bool
    {
        /** @var GarageProUser $garageMembership */
        foreach ($this->garageMemberships as $garageMembership) {
            if ($garageMembership->getGarage() === $garage) {
                return true;
            }
        }
        return false;
    }
}
