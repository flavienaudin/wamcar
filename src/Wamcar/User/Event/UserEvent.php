<?php


namespace Wamcar\User\Event;


use Wamcar\User\User;

interface UserEvent
{
    /**
     * UserEvent constructor.
     * @param User $user
     */
    public function __construct(User $user);

    /**
     * @return User
     */
    public function getUser(): User;
}
