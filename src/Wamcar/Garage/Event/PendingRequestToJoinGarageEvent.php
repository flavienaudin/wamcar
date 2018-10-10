<?php

namespace Wamcar\Garage\Event;


use Wamcar\Garage\GarageProUser;

interface PendingRequestToJoinGarageEvent
{

    /**
     * PendingRequestToJoinGarageEvent constructor.
     * @param GarageProUser $likeVehiclegarageProUser
     */
    public function __construct(GarageProUser $likeVehiclegarageProUser);

    /**
     * @return GarageProUser
     */
    public function getGarageProUser(): GarageProUser;
}