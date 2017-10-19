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
    public $city;
    /** @var  string */
    public $postalCode;

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

    public function fillFromUserProfile(UserProfile $profile)
    {
        $this->name = $profile->getName();
        $this->phone = $profile->getPhone();
        $this->fillFromCity($profile->getCity());
    }

    public function fillFromCity(City $city)
    {
        $this->city = $city->getName();
        $this->postalCode = $city->getPostalCode();
    }
}
