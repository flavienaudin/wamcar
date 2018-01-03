<?php

namespace Wamcar\User;

abstract class Picture
{
    /** @var BaseUser */
    protected $user;

    /**
     * Picture constructor.
     * @param BaseUser $user
     */
    public function __construct(BaseUser $user)
    {
        $this->user = $user;
    }
}
