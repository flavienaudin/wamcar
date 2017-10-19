<?php


namespace AppBundle\DTO\Form;


use AppBundle\Entity\ApplicationUser;

class EditUserData
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
     * EditUserData constructor.
     * @param ApplicationUser $user
     */
    public function __construct(ApplicationUser $user)
    {
        $this->id = $user->getId();
        $this->email = $user->getEmail();
        $this->title = $user->getTitle();
        $this->name = $user->getName();
        $this->phone = $user->getPhone();
        $this->city = $user->getCity()->getName();
        $this->postalCode = $user->getCity()->getPostalCode();
    }

}
