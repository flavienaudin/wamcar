<?php

namespace Wamcar\Garage\Event;


use Wamcar\User\Event\UserEvent;

class PendingRequestToJoinGarageCancelledEvent extends AbstractGarageMemberManagementEvent implements GarageMemberManagementEvent, UserEvent
{
}