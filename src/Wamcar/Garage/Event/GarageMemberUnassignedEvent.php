<?php

namespace Wamcar\Garage\Event;


use Wamcar\User\Event\UserEvent;

class GarageMemberUnassignedEvent extends AbstractGarageMemberManagementEvent implements GarageMemberManagementEvent, UserEvent
{
}