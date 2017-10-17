<?php

namespace AppBundle\Services\User;

use AppBundle\Doctrine\Entity\ApplicationUser;
use AppBundle\Form\DTO\UserInformationDTO;
use Wamcar\User\UserRepository;


class UserEditionService
{
    /** @var UserRepository  */
    private $userRepository;
    /** @var UserProfileService  */
    private $userProfileService;

    /**
     * UserEditionService constructor.
     *
     * @param UserRepository $userRepository
     * @param UserProfileService $userProfileService
     */
    public function __construct(
        UserRepository $userRepository,
        UserProfileService $userProfileService
    )
    {
        $this->userRepository = $userRepository;
        $this->userProfileService = $userProfileService;
    }

    /**
     * @param UserInformationDTO $userInformationDTO
     * @return ApplicationUser
     * @throws \Exception
     */
    public function editInformations(UserInformationDTO $userInformationDTO): ApplicationUser
    {
        /** @var ApplicationUser $applicationUser */
        $applicationUser = $this->userRepository->findOne($userInformationDTO->id);
        $userProfile = $this->userProfileService->transformToUserProfile($userInformationDTO);

        $applicationUser->setEmail($userInformationDTO->email);
        $applicationUser->setUserProfile($userProfile);

        $this->userRepository->update($applicationUser);

        return $applicationUser;
    }
}
