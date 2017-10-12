<?php


namespace AppBundle\Form\DTO;


class RegistrationData
{
    /** @var  string */
    public $email;
    /** @var  string */
    public $password;
    /** @var  string */
    public $ip;

    /**
     * RegistrationData constructor.
     *
     * @param string $email
     */
    public function __construct(string $ip, string $email = null)
    {
        $this->ip = $ip;
        $this->email = $email;
    }
}
