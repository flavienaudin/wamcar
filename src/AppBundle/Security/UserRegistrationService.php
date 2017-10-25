<?php

namespace AppBundle\Security;

use AppBundle\Doctrine\Entity\ApplicationUser;
use AppBundle\Doctrine\Entity\PersonalApplicationUser;
use AppBundle\Doctrine\Entity\ProApplicationUser;
use AppBundle\Form\DTO\RegistrationDTO;
use AppBundle\Utils\TokenGenerator;
use Psr\Log\LoggerInterface;
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
    /** @var LoggerInterface */
    private $logger;

    /**
     * UserRegistrationService constructor.
     * @param PasswordEncoderInterface $passwordEncoder
     * @param UserRepository $userRepository
     * @param MessageBus $eventBus
     * @param LoggerInterface $logger
     */
    public function __construct(
        PasswordEncoderInterface $passwordEncoder,
        UserRepository $userRepository,
        MessageBus $eventBus,
        LoggerInterface $logger
    )
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->userRepository = $userRepository;
        $this->eventBus = $eventBus;
        $this->logger = $logger;
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
            $this->logger->error($exception->getMessage());
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
