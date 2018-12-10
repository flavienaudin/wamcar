<?php

namespace AppBundle\Doctrine\Entity;

use AppBundle\Security\HasPasswordResettable;
use AppBundle\Services\User\CanBeGarageMember;
use AppBundle\Services\User\CanBeInConversation;
use Mgilet\NotificationBundle\Annotation\Notifiable;
use Mgilet\NotificationBundle\NotifiableInterface;
use Wamcar\Garage\Enum\GarageRole;
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
        foreach ($this->getGarageMemberships() as $member) {
            if ($member->getGarage() === $garage) {
                return $member;
            }
        }

        return null;
    }

    /**
     * @param Garage $garage
     * @param bool $includePending
     * @return bool
     */
    public function isMemberOfGarage(Garage $garage, bool $includePending = false): bool
    {
        if ($includePending) {
            $memberShips = $this->getGarageMemberships();
        } else {
            $memberShips = $this->getEnabledGarageMemberships();
        }
        /** @var GarageProUser $garageMembership */
        foreach ($memberShips as $garageMembership) {
            if ($garageMembership->getGarage() === $garage) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param Garage $garage
     * @return bool
     */
    public function isAdministratorOfGarage(Garage $garage): bool
    {
        /** @var GarageProUser $garageMembership */
        foreach ($this->getEnabledGarageMemberships() as $garageMembership) {
            if ($garageMembership->getGarage() === $garage) {
                return GarageRole::GARAGE_ADMINISTRATOR()->equals($garageMembership->getRole());
            }
        }
        return false;
    }
}
