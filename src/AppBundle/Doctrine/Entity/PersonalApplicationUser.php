<?php

namespace AppBundle\Doctrine\Entity;

use AppBundle\Security\HasPasswordResettable;
use AppBundle\Security\ShouldConfirmRegistration;
use AppBundle\Services\User\CanBeInConversation;
use AppBundle\Utils\TokenGenerator;
use Wamcar\User\PersonalUser;
use Wamcar\Vehicle\Vehicle;

class PersonalApplicationUser extends PersonalUser implements \Serializable, ShouldConfirmRegistration, ApplicationUser, HasPasswordResettable, CanBeInConversation
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
     */
    public function __construct(
        string $email,
        string $password,
        string $salt,
        string $firstName,
        string $name = null,
        Vehicle $firstVehicle = null
    )
    {
        parent::__construct($email, $firstName, $name, $firstVehicle);
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
