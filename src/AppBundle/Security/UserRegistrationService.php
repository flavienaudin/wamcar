<?php

namespace AppBundle\Security;

use AppBundle\Doctrine\Entity\ApplicationUser;
use AppBundle\Doctrine\Entity\PersonalApplicationUser;
use AppBundle\Doctrine\Entity\ProApplicationUser;
use AppBundle\Doctrine\Repository\DoctrineUserRepository;
use AppBundle\Form\DTO\RegistrationDTO;
use AppBundle\Utils\TokenGenerator;
use SimpleBus\Message\Bus\MessageBus;
use Wamcar\User\Event\UserCreated;
use Wamcar\User\UserRepository;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;

class UserRegistrationService
{
    /** @var PasswordEncoderInterface */
    private $passwordEncoder;
    /** @var UserRepository */
    private $userRepository;
    /** @var MessageBus */
    private $eventBus;

    /**
     * UserRegistrationService constructor.
     * @param PasswordEncoderInterface $passwordEncoder
     * @param UserRepository $userRepository
     * @param MessageBus $eventBus
     */
    public function __construct(
        PasswordEncoderInterface $passwordEncoder,
        UserRepository $userRepository,
        MessageBus $eventBus
    )
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->userRepository = $userRepository;
        $this->eventBus = $eventBus;
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

        try{
            $this->eventBus->handle(new UserCreated($applicationUser));
        } catch (\Exception $exception) {
            if($this->userRepository instanceof DoctrineUserRepository) {
                $this->userRepository->remove($applicationUser, true);
            }
            throw $exception;
        }

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
