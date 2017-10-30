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
     * @param PasswordResetDTO $passwordResetDTO
     *
     * @throws \Exception
     */
    public function editPassword(HasPasswordResettable $user, PasswordResetDTO $passwordResetDTO)
    {
        if (!$passwordResetDTO->password) {
            throw new \Exception('Password should be set for password editing');
        }

        $passwordResetDTO->salt = SaltGenerator::generateSalt();
        $passwordResetDTO->encodedPassword = $this->passwordEncoder->encodePassword($passwordResetDTO->password, $passwordResetDTO->salt);

        if ($user instanceof PersonalApplicationUser && $this->personalUserRepository instanceof UserWithResettablePasswordProvider) {
            $this->personalUserRepository->updatePassword($user, $passwordResetDTO->encodedPassword, $passwordResetDTO->salt);
        }
        elseif ($user instanceof ProApplicationUser && $this->proUserRepository instanceof UserWithResettablePasswordProvider) {
            $this->proUserRepository->updatePassword($user, $passwordResetDTO->encodedPassword, $passwordResetDTO->salt);
        }
    }

}
