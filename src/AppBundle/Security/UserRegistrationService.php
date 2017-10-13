<?php

namespace AppBundle\Security;

use AppBundle\Entity\ApplicationUser;
use AppBundle\Form\DTO\RegistrationData;
use AppBundle\Security\Repository\ShouldConfirmRegistration;
use AppBundle\Utils\TokenGenerator;
use Wamcar\User\UserRepository;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;

class UserRegistrationService
{
    /** @var PasswordEncoderInterface */
    private $passwordEncoder;
    /** @var UserRepository */
    private $userRepository;

    /**
     * UserRegistrationService constructor.
     *
     * @param PasswordEncoderInterface $passwordEncoder
     * @param UserRepository $userRepository
     */
    public function __construct(
        PasswordEncoderInterface $passwordEncoder,
        UserRepository $userRepository
    )
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->userRepository = $userRepository;
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
        $registrationToken = TokenGenerator::generateToken();

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
     * @param ShouldConfirmRegistration $user
     * @return ApplicationUser
     */
    public function confirmUserRegistration(ShouldConfirmRegistration $user): ApplicationUser
    {
        // confirm and save
        $user->confirmRegistration();
        $this->userRepository->update($user);

        return $user;
    }
}
