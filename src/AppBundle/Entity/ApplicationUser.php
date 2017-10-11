<?php

namespace AppBundle\Entity;

use Wamcar\User\City;
use Wamcar\User\Title;
use Wamcar\User\User;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;

class ApplicationUser extends User implements AdvancedUserInterface, \Serializable, AbleToLogin
{
    use Traits\PasswordResettableTrait;

    /** @var string */
    protected $registrationIp;
    /** @var string */
    protected $lastLoginIp;
    /** @var bool */
    protected $newsletterOptin;
    /** @var  string */
    protected $registrationToken;
    /** @var  string */
    protected $slug;

    /**
     * ApplicationUser constructor.
     *
     * @param string $email
     * @param Title|string $title
     * @param string $name
     * @param string $phone
     * @param string|null $password
     * @param string|null $salt
     * @param City $city
     * @param string $registrationIp
     * @param bool $newsletterOptin
     * @param string $registrationToken
     * @param array $roles
     */
    public function __construct(
        string $email,
        Title $title = null,
        string $name = null,
        string $phone = null,
        string $password = null,
        string $salt = null,
        City $city = null,
        string $registrationIp,
        bool $newsletterOptin = false,
        string $registrationToken = null,
        array  $roles = ['ROLE_USER']
    )
    {
        parent::__construct($email, $title, $name, $phone, $city, $roles);

        $this->password = $password;
        $this->salt = $salt;
        $this->registrationIp = $registrationIp;
        $this->lastLoginIp = $registrationIp;
        $this->newsletterOptin = $newsletterOptin;
        $this->registrationToken = $registrationToken;
    }

    /**
     * {@inheritdoc}
     */
    public function getPassword()
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
     * {@inheritdoc}
     */
    public function getNewsletterOptin()
    {
        return $this->newsletterOptin;
    }


    /**
     * @return ApplicationUser
     */
    public function enableNewsletterOptin(): ApplicationUser
    {
        $this->newsletterOptin = true;

        return $this;
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
     * @return string
     */
    public function getSlug(): string
    {
        return $this->slug;
    }
}
