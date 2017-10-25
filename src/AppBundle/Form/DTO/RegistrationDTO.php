<?php


namespace AppBundle\Form\DTO;


class RegistrationDTO
{
    const TYPE_PERSONAL = 'personal';
    const TYPE_PRO = 'pro';

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
    public function __construct($type = self::TYPE_PERSONAL)
    {
        $this->type = $type;
    }


}
