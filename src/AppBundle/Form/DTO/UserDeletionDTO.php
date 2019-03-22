<?php

namespace AppBundle\Form\DTO;


class UserDeletionDTO
{

    /** @var null|string */
    private $reason;

    /** @var boolean */
    private $confirmation;

    /**
     * UserDeletionDTO constructor.
     * @param string $reason
     * @param bool $confirmation
     */
    public function __construct(?string $reason = null, ?bool $confirmation = false)
    {
        $this->reason = $reason;
        $this->confirmation = $confirmation;
    }

    /**
     * @return null|string
     */
    public function getReason(): ?string
    {
        return $this->reason;
    }

    /**
     * @param null|string $reason
     */
    public function setReason(?string $reason): void
    {
        $this->reason = $reason;
    }

    /**
     * @return bool
     */
    public function isConfirmation(): bool
    {
        return $this->confirmation;
    }

    /**
     * @param bool $confirmation
     */
    public function setConfirmation(bool $confirmation): void
    {
        $this->confirmation = $confirmation;
    }
}