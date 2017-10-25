<?php

namespace AppBundle\Doctrine\Entity;

use AppBundle\Security\ShouldConfirmRegistration;
use AppBundle\Utils\TokenGenerator;
use Symfony\Component\Security\Core\User\UserInterface;
use Wamcar\User\PersonalUser;
use Wamcar\Vehicle\Vehicle;

class PersonalApplicationUser extends PersonalUser implements \Serializable, ShouldConfirmRegistration, ApplicationUser, UserInterface
{
    use ApplicationUserTrait;

    /**
     * ApplicationUser constructor.
     * @param string $email
     * @param string $password
     * @param string $salt
     * @param Vehicle|null $firstVehicle
     * @param string $registrationToken
     */
    public function __construct(
        string $email,
        string $password,
        string $salt,
        Vehicle $firstVehicle = null,
        string $registrationToken
    )
    {
        parent::__construct($email, $firstVehicle);

        $this->password = $password;
        $this->salt = $salt;
        $this->registrationToken = TokenGenerator::generateToken();;
    }

    /**
     * @return bool
     */
    public function hasConfirmedRegistration(): bool
    {
        return $this->registrationToken === null;
    }
}
