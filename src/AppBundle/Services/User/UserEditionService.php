<?php

namespace AppBundle\Services\User;

use AppBundle\Doctrine\Entity\ApplicationUser;
use AppBundle\Form\DTO\UserInformationDTO;
use Wamcar\User\City;
use Wamcar\User\Title;
use Wamcar\User\UserProfile;
use Wamcar\User\UserRepository;


class UserEditionService
{
    /** @var UserRepository  */
    private $userRepository;

    /**
     * UserEditionService constructor.
     *
     * @param UserRepository $userRepository
     */
    public function __construct(
        UserRepository $userRepository
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
        $title = new Title($userInformationDTO->title);
        $city = (!empty($userInformationDTO->postalCode)? new City($userInformationDTO->postalCode, $userInformationDTO->city): null);

        $userProfile = new UserProfile($title, $userInformationDTO->name, $userInformationDTO->phone, $city);
        $user->setEmail($userInformationDTO->email);
        $user->updateUserProfile($userProfile);

        $this->userRepository->update($user);

        return $user;
    }
}
