<?php

namespace Wamcar\Garage\Event;


use Wamcar\Garage\GarageProUser;

abstract class AbstractGarageMemberManagementEvent
{
    /** @var GarageProUser $garageProUser */
    private $garageProUser;

    /**
     * @inheritdoc
     */
    public function __construct(GarageProUser $garageProUser)
    {
        $this->garageProUser = $garageProUser;
    }

    /**
     * @inheritdoc
     */
    public function getGarageProUser(): GarageProUser
    {
        return $this->garageProUser;
    }
}