<?php


namespace AppBundle\Form\DTO;


use Wamcar\User\ProUser;

class ProContactMessageDTO
{

    /** @var ProUser */
    public $proUser;
    /** @var string|null */
    public $firstname;
    /** @var string|null */
    public $lastname;
    /** @var string|null */
    public $phonenumber;
    /** @var string|null */
    public $email;
    /** @var string|null */
    public $message;

    /**
     * ProContactMessageDTO constructor.
     * @param ProUser $proUser
     * @param string|null $firstname
     * @param string|null $lastname
     * @param string|null $phonenumber
     * @param string|null $email
     * @param string|null $message
     */
    public function __construct(ProUser $proUser, ?string $firstname = null, ?string $lastname = null, ?string $phonenumber = null, ?string $email = null, ?string $message = null)
    {
        $this->proUser = $proUser;
        $this->firstname = $firstname;
        $this->lastname = $lastname;
        $this->phonenumber = $phonenumber;
        $this->email = $email;
        $this->message = $message;
    }


}