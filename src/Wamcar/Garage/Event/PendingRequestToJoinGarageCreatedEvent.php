<?php

namespace Wamcar\Garage\Event;


use Wamcar\User\Event\UserEvent;

class PendingRequestToJoinGarageCreatedEvent extends AbstractGarageMemberManagementEvent implements GarageMemberManagementEvent, UserEvent
{

}