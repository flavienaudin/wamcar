<?php

namespace AppBundle\Services\User;

use AppBundle\Form\DTO\UserInformationDTO;
use Wamcar\User\City;
use Wamcar\User\Title;
use Wamcar\User\UserProfile;


class UserProfileService
{

    /**
     * @param UserInformationDTO $userInformationDTO
     * @return UserProfile
     * @throws \Exception
     */
    public function transformToUserProfile(UserInformationDTO $userInformationDTO): UserProfile
    {
        $userProfileDTO = $userInformationDTO->userProfileDTO;

        $title = new Title($userProfileDTO->title);
        $city = new City($userProfileDTO->postalCode, $userProfileDTO->city);

        $userProfile = new UserProfile($title, $userProfileDTO->name, $userProfileDTO->phone, $city);

        return $userProfile;
    }
}
