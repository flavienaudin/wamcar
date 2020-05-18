<?php


namespace AppBundle\Form\DTO;


use Wamcar\User\PersonalUser;

class RegistrationDTO
{

    /** @var  string */
    public $email;
    /** @var  string */
    public $password;
    /** @var  string */
    public $type;
    /** @var  string */
    public $firstName;
    /** @var  ?string */
    public $lastName;
    /** @var  ?string */
    public $socialNetworkOrigin;
    /** @var  ?string */
    public $target_path;
    /**
     * RegistrationDTO constructor.
     * @param string $type
     */
    public function __construct($type = PersonalUser::TYPE)
    {
        $this->type = $type;
    }


}
