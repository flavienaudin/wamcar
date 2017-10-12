<?php


namespace AppBundle\Form\DTO;


class RegistrationData
{
    /** @var  string */
    public $email;
    /** @var  string */
    public $password;

    /**
     * RegistrationData constructor.
     * @param string|null $email
     */
    public function __construct(string $email = null)
    {
        $this->email = $email;
    }
}
