<?php

namespace AppBundle\Security;

use AppBundle\DTO\Form\RegistrationData;
use AppBundle\Entity\ApplicationUser;
use AppBundle\Entity\RegisteredUser;
use AppBundle\Utils\TokenUtils;
use Wamcar\User\UserRepository;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;

class UserRegistrationService
{
    /** @var PasswordEncoderInterface */
    private $passwordEncoder;
    /** @var UserRepository */
    private $userRepository;
    /** @var TokenStorageInterface */
    private $tokenStorage;
    /** @var SessionInterface */
    private $session;

    /**
     * UserRegistrationService constructor.
     *
     * @param PasswordEncoderInterface $passwordEncoder
     * @param UserRepository $userRepository
     * @param TokenStorageInterface $tokenStorage
     * @param SessionInterface $session
     */
    public function __construct(
        PasswordEncoderInterface $passwordEncoder,
        UserRepository $userRepository,
        TokenStorageInterface $tokenStorage,
        SessionInterface $session
    )
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->userRepository = $userRepository;
        $this->tokenStorage = $tokenStorage;
        $this->session = $session;
    }

    /**
     * @param RegistrationData $registrationData
     *
     * @return RegisteredUser
     * @throws \Exception
     */
    public function registerUser(RegistrationData $registrationData): RegisteredUser
    {
        $salt = uniqid(mt_rand(), true);
        $encodedPassword = $this->passwordEncoder->encodePassword($registrationData->password, $salt);

        $registrationToken = TokenUtils::generateToken();


        $registeredUser = new RegisteredUser(
            $registrationData->email,
            null,
            null,
            null,
            $encodedPassword,
            $salt,
            null,
            $registrationData->ip,
            false,
            $registrationToken
        );

        $registeredUser = $this->userRepository->add($registeredUser);

        return $registeredUser;
    }

    /**
     * Confirm user registration
     * Don't log him in immediately, as the user has not entered credentials
     *
     * @param ApplicationUser $user
     * @return \Wamcar\User\User
     */
    public function confirmUserRegistration(ApplicationUser $user)
    {
        // confirm and save
        $user->confirmRegistration();
        $registeredUser = $this->userRepository->update($user);

        return $registeredUser;
    }
}
