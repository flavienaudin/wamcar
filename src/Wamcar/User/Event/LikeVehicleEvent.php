<?php

namespace Wamcar\User\Event;


use Wamcar\User\BaseLikeVehicle;

interface LikeVehicleEvent
{
    /**
     * AbstractLikeVehicleEvent constructor.
     * @param BaseLikeVehicle $likeVehicle
     */
    public function __construct(BaseLikeVehicle $likeVehicle);

    /**
     * @return BaseLikeVehicle
     */
    public function getLikeVehicle(): BaseLikeVehicle;
}