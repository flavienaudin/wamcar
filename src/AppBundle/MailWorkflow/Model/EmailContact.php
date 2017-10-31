<?php

namespace AppBundle\MailWorkflow\Model;

class EmailContact
{
    /** @var array */
    private $email;

    /** @var string */
    private $name;
    /**
     * @param string $email
     * @param string|null  $name
     */
    public function __construct(string $email, string $name = null)
    {
        $this->email = $email;
        $this->name  = $name;
    }
    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }
    /**
     * @return string
     */
    public function getName(): ?string
    {
        return $this->name;
    }
}
