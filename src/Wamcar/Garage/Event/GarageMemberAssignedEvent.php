<?php

namespace Wamcar\Garage\Event;


use Wamcar\User\Event\UserEvent;

class GarageMemberAssignedEvent extends AbstractGarageMemberManagementEvent implements GarageMemberManagementEvent, UserEvent
{
}