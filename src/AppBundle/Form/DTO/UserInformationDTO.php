<?php


namespace AppBundle\Form\DTO;


use AppBundle\Entity\ApplicationUser;

class UserInformationDTO
{
    /** @var  int */
    public $id;
    /** @var  string */
    public $email;
    /** @var  UserProfileDTO */
    public $userProfileDTO;

    /**
     * UserInformationDTO constructor.
     * @param ApplicationUser $user
     */
    public function __construct(ApplicationUser $user)
    {
        $this->id = $user->getId();
        $this->email = $user->getEmail();
        $this->userProfileDTO = new UserProfileDTO($user);
    }

}
