<?php


namespace Wamcar\User\Event;


use Wamcar\User\BaseUser;

interface UserEvent
{
    /**
     * @return BaseUser
     */
    public function getUser(): BaseUser;
}
