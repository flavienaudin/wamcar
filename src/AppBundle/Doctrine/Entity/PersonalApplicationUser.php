<?php

namespace AppBundle\Doctrine\Entity;

use AppBundle\Security\HasPasswordResettable;
use AppBundle\Security\ShouldConfirmRegistration;
use AppBundle\Services\User\CanBeInConversation;
use AppBundle\Utils\TokenGenerator;
use Mgilet\NotificationBundle\Annotation\Notifiable;
use Mgilet\NotificationBundle\NotifiableInterface;
use Wamcar\Location\City;
use Wamcar\User\PersonalUser;
use Wamcar\Vehicle\Vehicle;

/**
 * Class PersonalApplicationUser
 * @package AppBundle\Doctrine\Entity
 * @Notifiable(name="PersonalApplicationUser")
 */
class PersonalApplicationUser extends PersonalUser implements \Serializable, ShouldConfirmRegistration, ApplicationUser, HasPasswordResettable, CanBeInConversation, NotifiableInterface
{
    use ApplicationUserTrait;
    use PasswordResettableTrait;

    /**
     * ApplicationUser constructor.
     * @param string $email
     * @param string $password
     * @param string $salt
     * @param string $firstName
     * @param string|null $name
     * @param Vehicle|null $firstVehicle
     * @param City|null $city
     */
    public function __construct(
        string $email,
        string $password,
        string $salt,
        string $firstName,
        string $name = null,
        Vehicle $firstVehicle = null,
        City $city = null
    )
    {
        parent::__construct($email, $firstName, $name, $firstVehicle, $city);
        $this->password = $password;
        $this->salt = $salt;
        $this->registrationToken = TokenGenerator::generateToken();
    }

    /**
     * @return bool
     */
    public function hasConfirmedRegistration(): bool
    {
        return $this->registrationToken === null;
    }

    /**
     * @return array
     */
    public function getRoles(): array
    {
        return ['ROLE_USER'];
    }
}
