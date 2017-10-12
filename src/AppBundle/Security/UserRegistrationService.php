<?php

namespace AppBundle\Security;

use AppBundle\Entity\ApplicationUser;
use AppBundle\Form\DTO\RegistrationData;
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
     * @return ApplicationUser
     * @throws \Exception
     */
    public function registerUser(RegistrationData $registrationData): ApplicationUser
    {
        $salt = uniqid(mt_rand(), true);
        $encodedPassword = $this->passwordEncoder->encodePassword($registrationData->password, $salt);

        $registrationToken = TokenUtils::generateToken();


        $applicationUser = new ApplicationUser(
            $registrationData->email,
            $encodedPassword,
            $salt,
            null,
            $registrationToken
        );

        $this->userRepository->add($applicationUser);

        return $applicationUser;
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
        $this->userRepository->update($user);

        return $user;
    }
}
