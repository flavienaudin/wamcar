<?php

namespace AppBundle\Security;

use AppBundle\Doctrine\Entity\ApplicationUser;
use AppBundle\Doctrine\Entity\PersonalApplicationUser;
use AppBundle\Doctrine\Entity\ProApplicationUser;
use AppBundle\Form\DTO\RegistrationDTO;
use AppBundle\Utils\TokenGenerator;
use Wamcar\User\BaseUserRepository;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;

class UserRegistrationService
{
    /** @var PasswordEncoderInterface */
    private $passwordEncoder;
    /** @var BaseUserRepository */
    private $userRepository;

    /**
     * UserRegistrationService constructor.
     *
     * @param PasswordEncoderInterface $passwordEncoder
     * @param BaseUserRepository $userRepository
     */
    public function __construct(
        PasswordEncoderInterface $passwordEncoder,
        BaseUserRepository $userRepository
    )
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->userRepository = $userRepository;
    }

    /**
     * @param RegistrationDTO $registrationDTO
     * @return ApplicationUser
     * @throws \Exception
     */
    public function registerUser(RegistrationDTO $registrationDTO): ApplicationUser
    {
        $salt = uniqid(mt_rand(), true);
        $encodedPassword = $this->passwordEncoder->encodePassword($registrationDTO->password, $salt);
        $registrationToken = TokenGenerator::generateToken();

        $applicationUser = null;
        if ($registrationDTO->type ==='personal') {
            $applicationUser = new PersonalApplicationUser(
                $registrationDTO->email,
                $encodedPassword,
                $salt,
                null,
                $registrationToken
            );
        } elseif ($registrationDTO->type ==='pro') {
            $applicationUser = new ProApplicationUser(
                $registrationDTO->email,
                $encodedPassword,
                $salt
            );
        }

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
