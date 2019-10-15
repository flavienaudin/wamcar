<?php


namespace Wamcar\Conversation;


use Gedmo\Timestampable\Traits\TimestampableEntity;
use Wamcar\User\ProUser;

class ProContactMessage
{
    use TimestampableEntity;

    /** @var null|int */
    private $id;
    /** @var ProUser */
    private $proUser;
    /** @var string */
    private $firstname;
    /** @var string|null */
    private $lastname;
    /** @var string|null */
    private $phonenumber;
    /** @var string */
    private $email;
    /** @var string */
    private $message;

    /**
     * ProContactMessage constructor.
     * @param ProUser $proUser
     * @param string $firstname
     * @param string|null $lastname
     * @param string|null $phonenumber
     * @param string $email
     * @param string $message
     */
    public function __construct(ProUser $proUser, string $firstname, ?string $lastname, ?string $phonenumber, string $email, string $message)
    {
        $this->proUser = $proUser;
        $this->firstname = $firstname;
        $this->lastname = $lastname;
        $this->phonenumber = $phonenumber;
        $this->email = $email;
        $this->message = $message;
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return ProUser
     */
    public function getProUser(): ProUser
    {
        return $this->proUser;
    }

    /**
     * @param ProUser $proUser
     */
    public function setProUser(ProUser $proUser): void
    {
        $this->proUser = $proUser;
    }

    /**
     * @return string
     */
    public function getFirstname(): string
    {
        return $this->firstname;
    }

    /**
     * @param string $firstname
     */
    public function setFirstname(string $firstname): void
    {
        $this->firstname = $firstname;
    }

    /**
     * @return string|null
     */
    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    /**
     * @param string|null $lastname
     */
    public function setLastname(?string $lastname): void
    {
        $this->lastname = $lastname;
    }

    /**
     * @return string|null
     */
    public function getPhonenumber(): ?string
    {
        return $this->phonenumber;
    }

    /**
     * @param string|null $phonenumber
     */
    public function setPhonenumber(?string $phonenumber): void
    {
        $this->phonenumber = $phonenumber;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @param string $message
     */
    public function setMessage(string $message): void
    {
        $this->message = $message;
    }


}