<?php

namespace Wamcar\User\Event;


interface InvitationsEventHandler
{
    /**
     * @param InvitationsEvent $invitationsEvent
     */
    public function notify(InvitationsEvent $invitationsEvent);
}