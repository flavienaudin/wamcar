<?php

namespace AppBundle\Doctrine\Entity;

use AppBundle\DTO\Form\EditUserData;
use AppBundle\Security\ShouldConfirmRegistration;
use Wamcar\User\City;
use Wamcar\User\Title;
use Wamcar\User\User;
use Wamcar\Vehicle\Vehicle;

class ApplicationUser extends User implements \Serializable, ShouldConfirmRegistration
{
    /** @var string */
    protected $password;
    /** @var string */
    protected $salt;
    /** @var  string */
    protected $registrationToken;
    /** @var  \DateTime */
    protected $createdAt;
    /** @var  \DateTime */
    protected $deletedAt;

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
        $this->registrationToken = $registrationToken;
    }

    /**
     * {@inheritdoc}
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * {@inheritdoc}
     */
    public function getSalt()
    {
        return $this->salt;
    }

    /**
     * {@inheritdoc}
     */
    public function getUsername(): string
    {
        return $this->email;
    }

    /**
     * @return string
     */
    public function getRegistrationToken()
    {
        return $this->registrationToken;
    }

    /**
     * {@inheritdoc}
     */
    public function eraseCredentials()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function isAccountNonExpired()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isAccountNonLocked()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isCredentialsNonExpired()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabled()
    {
        // if a registration token is set, the registration process is not finished
        // we can't consider the user enabled
        return $this->getRegistrationToken() === null;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize(): string
    {
        return serialize(array(
            $this->id,
            $this->email,
            $this->password,
            $this->salt,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized)
    {
        list (
            $this->id,
            $this->email,
            $this->password,
            $this->salt
            ) = unserialize($serialized);
    }

    /**
     * Registration confirmation equals nullifying registrationToken
     *
     * @return $this
     */
    public function confirmRegistration()
    {
        $this->registrationToken = null;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasConfirmedRegistration(): bool
    {
        return $this->registrationToken === null;
    }

    /**
     * Copy informations from EditUserData
     *
     * @param EditUserData $userData
     */
    public function updateInformations(EditUserData $userData)
    {
        $city = new City(
            $userData->postalCode,
            $userData->city
        );

        $this->email = $userData->email;
        $this->title = new Title($userData->title);
        $this->name = $userData->name;
        $this->phone = $userData->phone;
        $this->city = $city;
        $this->lastLoginIp = $userData->ip;
        $this->newsletterOptin = $userData->newsletterOptin;

        if ($userData->encodedPassword) {
            // update password and salt if needed
            $this->resetPassword($userData->encodedPassword, $userData->salt);
        }
    }

}
