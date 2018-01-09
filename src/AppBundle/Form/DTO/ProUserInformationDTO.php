<?php


namespace AppBundle\Form\DTO;

use AppBundle\Doctrine\Entity\ApplicationUser;

class ProUserInformationDTO extends UserInformationDTO
{

    /** @var  string */
    public $phonePro;

    /**
     * UserInformationDTO constructor.
     * @param ApplicationUser $user
     */
    public function __construct(ApplicationUser $user)
    {
        parent::__construct($user);
        $this->phonePro = $user->getPhonePro();

    }
}
