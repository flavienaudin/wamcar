<?php


namespace AppBundle\Form\DTO;

use AppBundle\Doctrine\Entity\ApplicationUser;
use Wamcar\User\City;
use Wamcar\User\UserProfile;

class UserInformationDTO
{
    /** @var  int */
    public $id;
    /** @var  string */
    public $email;
    /** @var  string */
    public $title;
    /** @var  string */
    public $name;
    /** @var  string */
    public $phone;
    /** @var  string */
    public $cityName;
    /** @var  string */
    public $postalCode;
    /** @var  string */
    public $oldPassword;
    /** @var  string */
    public $newPassword;


    /**
     * UserInformationDTO constructor.
     * @param ApplicationUser $user
     */
    public function __construct(ApplicationUser $user)
    {
        $this->id = $user->getId();
        $this->email = $user->getEmail();
        $this->fillFromUserProfile($user->getUserProfile());
    }

    /**
     * @param UserProfile $profile
     */
    public function fillFromUserProfile(UserProfile $profile)
    {
        $this->name = $profile->getName();
        $this->phone = $profile->getPhone();
        $this->title = $profile->getTitle();
        $this->fillFromCity($profile->getCity());
    }

    /**
     * @param City $city
     */
    public function fillFromCity(City $city)
    {
        $this->cityName = $city->getName();
        $this->postalCode = $city->getPostalCode();
    }

    /**
     * @return null|City
     */
    public function getCity(): ?City
    {
        $city = null;

        if (!empty($this->postalCode) && !empty($this->cityName))
            $city = new City($this->postalCode, $this->cityName);

        return $city;
    }

    /**
     * @return UserProfile
     */
    public function getUserProfile(): UserProfile
    {
        $userProfile = new UserProfile($this->title, $this->name, $this->phone, $this->getCity());

        return $userProfile;
    }
}
