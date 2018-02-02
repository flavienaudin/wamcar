<?php

namespace AppBundle\Doctrine\Entity;

use AppBundle\Security\HasPasswordResettable;
use AppBundle\Security\ShouldConfirmRegistration;
use AppBundle\Services\User\CanBeInConversation;
use AppBundle\Utils\TokenGenerator;
use Symfony\Component\Security\Core\User\UserInterface;
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
     * @param Vehicle|null $firstVehicle
     */
    public function __construct(
        string $email,
        string $password,
        string $salt,
        Vehicle $firstVehicle = null
    )
    {
        parent::__construct($email, $firstVehicle);

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
