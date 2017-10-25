<?php


namespace Wamcar\User\Event;


use Wamcar\User\BaseUser;

interface UserEvent
{
    /**
     * UserEvent constructor.
     * @param BaseUser $user
     */
    public function __construct(BaseUser $user);

    /**
     * @return BaseUser
     */
    public function getUser(): BaseUser;
}
