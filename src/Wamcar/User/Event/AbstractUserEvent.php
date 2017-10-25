<?php

namespace Wamcar\User\Event;

use Wamcar\User\BaseUser;

abstract class AbstractUserEvent
{
    /** @var BaseUser */
    private $user;

    /**
     * AbstractUserEvent constructor.
     * @param BaseUser $user
     */
    public function __construct(BaseUser $user)
    {
        $this->user = $user;
    }

    /**
     * @return BaseUser
     */
    public function getUser(): BaseUser
    {
        return $this->user;
    }
}
