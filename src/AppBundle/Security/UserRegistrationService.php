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
    private $passwordEncoderPro;
    /** @var PasswordEncoderInterface */
    private $passwordEncoderPersonal;
    /** @var BaseUserRepository */
    private $userRepository;

    /**
     * UserRegistrationService constructor.
     *
     * @param PasswordEncoderInterface $passwordEncoderPro
     * @param PasswordEncoderInterface $passwordEncoderPersonal
     * @param BaseUserRepository $userRepository
     */
    public function __construct(
        PasswordEncoderInterface $passwordEncoderPro,
        PasswordEncoderInterface $passwordEncoderPersonal,
        BaseUserRepository $userRepository
    )
    {
        $this->passwordEncoderPro = $passwordEncoderPro;
        $this->passwordEncoderPersonal = $passwordEncoderPersonal;
        $this->userRepository = $userRepository;
    }

    /**
     * @param RegistrationDTO $registrationDTO
     * @return ProApplicationUser
     * @throws \Exception
     */
    public function registerUserPro(RegistrationDTO $registrationDTO): ProApplicationUser
    {
        $salt = uniqid(mt_rand(), true);
        $encodedPassword = $this->passwordEncoderPro->encodePassword($registrationDTO->password, $salt);
        $registrationToken = TokenGenerator::generateToken();

        $proApplicationUser = new ProApplicationUser(
            $registrationDTO->email,
            $encodedPassword,
            $salt,
            $registrationToken
        );

        $this->userRepository->add($proApplicationUser);

        return $proApplicationUser;
    }

    /**
     * @param RegistrationDTO $registrationDTO
     * @return PersonalApplicationUser
     * @throws \Exception
     */
    public function registerUserPersonal(RegistrationDTO $registrationDTO): PersonalApplicationUser
    {
        $salt = uniqid(mt_rand(), true);
        $encodedPassword = $this->passwordEncoderPersonal->encodePassword($registrationDTO->password, $salt);
        $registrationToken = TokenGenerator::generateToken();

        $personnalApplicationUser = new PersonalApplicationUser(
            $registrationDTO->email,
            $encodedPassword,
            $salt,
            null,
            $registrationToken
        );

        $this->userRepository->add($personnalApplicationUser);

        return $personnalApplicationUser;
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
