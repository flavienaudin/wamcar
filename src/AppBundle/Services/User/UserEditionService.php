<?php

namespace AppBundle\Services\User;

use AppBundle\Doctrine\Entity\ApplicationUser;
use AppBundle\Doctrine\Entity\UserPicture;
use AppBundle\Form\DTO\ProjectDTO;
use AppBundle\Form\DTO\ProUserInformationDTO;
use AppBundle\Form\DTO\UserInformationDTO;
use AppBundle\Security\HasPasswordResettable;
use AppBundle\Security\Repository\UserWithResettablePasswordProvider;
use AppBundle\Utils\TokenGenerator;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;
use Wamcar\User\UserRepository;


class UserEditionService
{
    /** @var PasswordEncoderInterface */
    private $passwordEncoder;
    /** @var UserRepository  */
    private $userRepository;
    /** @var array  */
    private $userSpecificRepositories;

    /**
     * UserEditionService constructor.
     * @param PasswordEncoderInterface $passwordEncoder
     * @param UserRepository $userRepository
     * @param array $userSpecificRepositories
     */
    public function __construct(
        PasswordEncoderInterface $passwordEncoder,
        UserRepository $userRepository,
        array $userSpecificRepositories
    )
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->userRepository = $userRepository;
        $this->userSpecificRepositories = $userSpecificRepositories;
    }

    /**
     * @param ApplicationUser $user
     * @param UserInformationDTO $userInformationDTO
     * @return ApplicationUser
     * @throws \Exception
     */
    public function editInformations(ApplicationUser $user, UserInformationDTO $userInformationDTO): ApplicationUser
    {
        if (!empty($userInformationDTO->newPassword)) {
            $isValid = $this->passwordEncoder->isPasswordValid($user->getPassword(), $userInformationDTO->oldPassword, $user->getSalt());
            if (!$isValid) {
                throw new \InvalidArgumentException('Password should be the current');
            }
            $this->editPassword($user, $userInformationDTO->newPassword);
        }

        $user->setEmail($userInformationDTO->email);
        $user->updateUserProfile($userInformationDTO->getUserProfile());

        if ($userInformationDTO->avatar) {
            $picture = new UserPicture($user, $userInformationDTO->avatar);
            $user->setAvatar($picture);
        }

        if ($userInformationDTO instanceof ProUserInformationDTO) {
            $user->setPhonePro($userInformationDTO->phonePro);
        }

        $this->userRepository->update($user);

        return $user;
    }

    /**
     * @param ApplicationUser $user
     * @param ProjectDTO $projectDTO
     * @return ApplicationUser
     * @throws \Exception
     */
    public function projectInformations(ApplicationUser $user, ProjectDTO $projectDTO): ApplicationUser
    {
        //@TODO making all the registration of th project
        return $user;
    }

    /**
     * @param HasPasswordResettable $user
     * @return HasPasswordResettable
     */
    public function generatePasswordResetToken(HasPasswordResettable $user): HasPasswordResettable
    {
        $user->generatePasswordResetToken();
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

        $userSpecificRepository = $this->userSpecificRepositories[get_class($user)];
        if (!$userSpecificRepository instanceof UserWithResettablePasswordProvider) {
            throw new \InvalidArgumentException(sprintf('$user can only be updated by object implementing the "%s" interface', UserWithResettablePasswordProvider::class));
        }

        $userSpecificRepository->updatePassword($user, $encodedPassword, $salt);
    }

}
