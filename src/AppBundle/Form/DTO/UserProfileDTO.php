<?php


namespace AppBundle\Form\DTO;

use AppBundle\Doctrine\Entity\ApplicationUser;

class UserProfileDTO
{
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
        $this->title = $user->getUserProfile()->getTitle();
        $this->name = $user->getUserProfile()->getName();
        $this->phone = $user->getUserProfile()->getPhone();
        $this->city = $user->getUserProfile()->getCity()->getName();
        $this->postalCode = $user->getUserProfile()->getCity()->getPostalCode();
    }

}
