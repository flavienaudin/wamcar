<?php


namespace AppBundle\Form\DTO;

use AppBundle\Doctrine\Entity\ApplicationUser;
use Wamcar\Location\City;
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
    public $firstName;
    /** @var  string */
    public $lastName;
    /** @var  string */
    public $description;
    /** @var  string */
    public $phone;
    /** @var  string */
    public $cityName;
    /** @var  string */
    public $postalCode;
    /** @var string */
    public $latitude;
    /** @var string */
    public $longitude;
    /** @var  string */
    public $oldPassword;
    /** @var  string */
    public $newPassword;
    /** @var UserPictureDTO */
    public $avatar;


    /**
     * UserInformationDTO constructor.
     * @param ApplicationUser $user
     */
    public function __construct(ApplicationUser $user)
    {
        $this->id = $user->getId();
        $this->email = $user->getEmail();
        $this->avatar = new UserPictureDTO($user->getAvatarFile());
        $this->fillFromUserProfile($user->getUserProfile());
    }

    /**
     * @param UserProfile $profile
     */
    public function fillFromUserProfile(UserProfile $profile)
    {
        $this->firstName = $profile->getFirstName();
        $this->lastName = $profile->getLastName();
        $this->phone = $profile->getPhone();
        $this->title = $profile->getTitle();
        $this->description = $profile->getDescription();
        $this->fillFromCity($profile->getCity());
    }

    /**
     * @param City $city
     */
    public function fillFromCity(City $city)
    {
        $this->cityName = $city->getName();
        $this->postalCode = $city->getPostalCode();
        $this->latitude = $city->getLatitude();
        $this->longitude = $city->getLongitude();
    }

    /**
     * @return null|City
     */
    public function getCity(): ?City
    {
        $city = null;

        if (!empty($this->postalCode) && !empty($this->cityName && !empty($this->latitude) && !empty($this->longitude))) {
            $city = new City($this->postalCode, $this->cityName, $this->latitude, $this->longitude);
        }

        return $city;
    }

    /**
     * @return UserProfile
     */
    public function getUserProfile(): UserProfile
    {
        $userProfile = new UserProfile($this->title, $this->firstName, $this->lastName, $this->description, $this->phone, $this->getCity());

        return $userProfile;
    }
}
