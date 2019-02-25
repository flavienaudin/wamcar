<?php

namespace Wamcar\Garage\Event;


use Wamcar\User\Event\UserEvent;

class PendingRequestToJoinGarageAcceptedEvent extends AbstractGarageMemberManagementEvent implements GarageMemberManagementEvent, UserEvent
{
}