<?php

namespace Wamcar\User\Event;

use Wamcar\User\User;

abstract class AbstractUserEvent
{
    /** @var User */
    private $user;

    /**
     * AbstractUserEvent constructor.
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }
}
