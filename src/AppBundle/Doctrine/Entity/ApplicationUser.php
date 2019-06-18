<?php

namespace AppBundle\Doctrine\Entity;

use Symfony\Component\Security\Core\User\UserInterface;

interface ApplicationUser extends UserInterface
{
    /**
     * {@inheritdoc}
     */
    public function getPassword(): string;

    /**
     * {@inheritdoc}
     */
    public function getSalt();

    /**
     * {@inheritdoc}
     */
    public function getUsername(): string;

    /**
     * @return string
     */
    public function getRegistrationToken();

    /**
     * @return \DateTime
     */
    public function getLastLoginAt(): \DateTime;

    /**
     * @param \DateTime $lastLoginAt
     */
    public function setLastLoginAt(\DateTime $lastLoginAt): void;

    /**
     * {@inheritdoc}
     */
    public function eraseCredentials();

    /**
     * {@inheritdoc}
     */
    public function isAccountNonExpired();

    /**
     * {@inheritdoc}
     */
    public function isAccountNonLocked();

    /**
     * {@inheritdoc}
     */
    public function isCredentialsNonExpired();

    /**
     * {@inheritdoc}
     */
    public function isEnabled();

    /**
     * {@inheritdoc}
     */
    public function serialize(): string;

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized);

    /**
     * Registration confirmation equals nullifying registrationToken
     *
     * @return $this
     */
    public function confirmRegistration();

    /**
     * @return bool
     */
    public function hasConfirmedRegistration(): bool;
}
