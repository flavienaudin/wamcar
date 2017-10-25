<?php

namespace AppBundle\Security;

use AppBundle\Doctrine\Entity\ApplicationUser;
use AppBundle\Doctrine\Entity\PersonalApplicationUser;
use AppBundle\Doctrine\Entity\ProApplicationUser;
use AppBundle\Form\DTO\RegistrationDTO;
use Psr\Log\LoggerInterface;
use SimpleBus\Message\Bus\MessageBus;
use Wamcar\User\Event\ProUserCreated;
use Wamcar\User\Event\UserCreated;
use Wamcar\User\UserRepository;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;
use Wamcar\User\PersonalUser;

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

        $userClassMapping = [
            'personal' => PersonalApplicationUser::class,
            'pro' => ProApplicationUser::class,
        ];

        
        $applicationUser = new $userClassMapping[$registrationDTO->type](
            $registrationDTO->email,
            $encodedPassword,
            $salt
        );
        $this->userRepository->add($applicationUser);

        try {
            if ($applicationUser instanceof PersonalUser) {
                $this->eventBus->handle(new UserCreated($applicationUser));
            } else {
                $this->eventBus->handle(new ProUserCreated($applicationUser));
            }
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
