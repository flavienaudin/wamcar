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
    public $password;
    /** @var  string */
    public $encodedPassword;
    /** @var  string */
    public $salt;
    /** @var  string */
    public $city;
    /** @var  string */
    public $postalCode;
    /** @var  string */
    public $ip;
    /** @var  bool */
    public $newsletterOptin;

    /**
     * EditUserData constructor.
     * @param ApplicationUser $user
     * @param $ip
     */
    public function __construct(ApplicationUser $user, $ip)
    {
        $this->id = $user->getId();
        $this->email = $user->getEmail();
        $this->title = $user->getTitle();
        $this->name = $user->getName();
        $this->phone = $user->getPhone();
        $this->city = $user->getCity()->getCity();
        $this->postalCode = $user->getCity()->getPostalCode();
        $this->ip = $ip;
        $this->newsletterOptin = $user->getNewsletterOptin();
    }

}
