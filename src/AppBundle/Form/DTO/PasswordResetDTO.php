<?php


namespace AppBundle\Form\DTO;

use AppBundle\Doctrine\Entity\ApplicationUser;
use AppBundle\Security\HasPasswordResettable;

class PasswordResetDTO
{
    /** @var  string */
    public $password;
    /** @var  string */
    public $salt;
    /** @var  string */
    public $encodedPassword;

}
