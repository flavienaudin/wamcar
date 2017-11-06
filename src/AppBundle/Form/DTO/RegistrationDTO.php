<?php


namespace AppBundle\Form\DTO;


class RegistrationDTO
{

    /** @var  string */
    public $email;
    /** @var  string */
    public $password;
    /** @var  string */
    public $type;

    /**
     * RegistrationDTO constructor.
     * @param string $type
     */
    public function __construct($type = 'personal')
    {
        $this->type = $type;
    }


}
