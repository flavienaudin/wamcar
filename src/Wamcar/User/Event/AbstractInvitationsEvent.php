<?php

namespace Wamcar\User\Event;


class AbstractInvitationsEvent
{
    /** @var array */
    private $invitation;

    /**
     * InvitationsEvent constructor.
     * @param array $invitations
     */
    public function __construct(array $invitations)
    {
        $this->invitation = $invitations;
    }

    /**
     * @return array Invitations
     */
    public function getInvitations(): array
    {
        return $this->invitation;
    }
}