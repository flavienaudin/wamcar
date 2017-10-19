<?php

namespace AppBundle\Services\User;

use AppBundle\Doctrine\Entity\ApplicationUser;
use AppBundle\Form\DTO\UserInformationDTO;
use Wamcar\User\BaseUserRepository;


class UserEditionService
{
    /** @var BaseUserRepository  */
    private $userRepository;

    /**
     * BaseUserRepository constructor.
     *
     * @param BaseUserRepository $userRepository
     */
    public function __construct(
        BaseUserRepository $userRepository
    )
    {
        $this->userRepository = $userRepository;
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
}
