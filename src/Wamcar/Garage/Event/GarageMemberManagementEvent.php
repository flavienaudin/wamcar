<?php

namespace Wamcar\Garage\Event;


use Wamcar\Garage\GarageProUser;

interface GarageMemberManagementEvent
{

    /**
     * GarageMemberManagementEvent constructor.
     * @param GarageProUser $garageProUser
     */
    public function __construct(GarageProUser $garageProUser);

    /**
     * @return GarageProUser
     */
    public function getGarageProUser(): GarageProUser;
}