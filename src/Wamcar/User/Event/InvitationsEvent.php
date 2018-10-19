<?php

namespace Wamcar\User\Event;


interface InvitationsEvent
{

    /**
     * @return array Invitations
     */
    public function getInvitations(): array;
}