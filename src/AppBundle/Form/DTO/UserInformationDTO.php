<?php


namespace AppBundle\Form\DTO;


use AppBundle\Entity\ApplicationUser;

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
        $this->title = $user->getUserProfile()->getTitle();
        $this->name = $user->getUserProfile()->getName();
        $this->phone = $user->getUserProfile()->getPhone();
        $this->city = $user->getUserProfile()->getCity()->getName();
        $this->postalCode = $user->getUserProfile()->getCity()->getPostalCode();
    }

}
