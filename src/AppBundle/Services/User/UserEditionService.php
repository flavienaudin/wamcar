<?php

namespace AppBundle\Services\User;

use AppBundle\Doctrine\Entity\ApplicationUser;
use AppBundle\Doctrine\Entity\PersonalApplicationUser;
use AppBundle\Doctrine\Entity\ProApplicationUser;
use AppBundle\Doctrine\Repository\DoctrinePersonalUserRepository;
use AppBundle\Doctrine\Repository\DoctrineProUserRepository;
use AppBundle\Form\DTO\PasswordResetDTO;
use AppBundle\Form\DTO\UserInformationDTO;
use AppBundle\Security\HasPasswordResettable;
use AppBundle\Security\Repository\UserWithResettablePasswordProvider;
use AppBundle\Utils\SaltGenerator;
use AppBundle\Utils\TokenGenerator;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;
use Wamcar\User\UserRepository;


class UserEditionService
{
    /** @var PasswordEncoderInterface */
    private $passwordEncoder;

    /** @var UserRepository  */
    private $userRepository;

    /** @var DoctrinePersonalUserRepository  */
    private $personalUserRepository;

    /** @var DoctrineProUserRepository  */
    private $proUserRepository;

    /** @var array  */
    private $userRepositories;

    /**
     * UserEditionService constructor.
     * @param PasswordEncoderInterface $passwordEncoder
     * @param UserRepository $userRepository
     * @param DoctrinePersonalUserRepository $personalUserRepository
     * @param DoctrineProUserRepository $proUserRepository
     */
    public function __construct(
        PasswordEncoderInterface $passwordEncoder,
        UserRepository $userRepository,
        DoctrinePersonalUserRepository $personalUserRepository,
        DoctrineProUserRepository $proUserRepository
    )
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->userRepository = $userRepository;
        $this->personalUserRepository = $personalUserRepository;
        $this->proUserRepository = $proUserRepository;
        $this->userRepositories = [
            PersonalApplicationUser::class => $personalUserRepository,
            ProApplicationUser::class => $proUserRepository
        ];
    }

    /**
     * @param ApplicationUser $user
     * @param UserInformationDTO $userInformationDTO
     * @return ApplicationUser
     */
    public function editInformations(ApplicationUser $user, UserInformationDTO $userInformationDTO): ApplicationUser
    {
        $user->setEmail($userInformationDTO->email);
        $user->updateUserProfile($userInformationDTO->getUserProfile());

        $this->userRepository->update($user);

        return $user;
    }

    /**
     * @param HasPasswordResettable $user
     * @return HasPasswordResettable
     */
    public function generatePasswordResetToken(HasPasswordResettable $user): HasPasswordResettable
    {
        $token = TokenGenerator::generateToken();
        $user->setPasswordResetToken($token);
        $this->userRepository->update($user);

        return $user;
    }

    /**
     * @param HasPasswordResettable $user
     * @param $password
     *
     * @throws \Exception
     */
    public function editPassword(HasPasswordResettable $user, $password)
    {
        if (!$password) {
            throw new \InvalidArgumentException('Password should be set for password editing');
        }

        $salt = TokenGenerator::generateSalt();
        $encodedPassword = $this->passwordEncoder->encodePassword($password, $salt);

        $userRepository = $this->userRepositories[get_class($user)];
        if (!$userRepository instanceof UserWithResettablePasswordProvider) {
            throw new \InvalidArgumentException(sprintf('$user can only be updated by object implementing the "%s" interface', UserWithResettablePasswordProvider::class));
        }

        $userRepository->updatePassword($user, $encodedPassword, $salt);
    }

}
