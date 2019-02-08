<?php

namespace Wamcar\Garage\Event;


use Wamcar\User\Event\UserEvent;

class PendingRequestToJoinGarageDeclinedEvent extends AbstractGarageMemberManagementEvent implements GarageMemberManagementEvent, UserEvent
{
}