<?php

namespace Wamcar\User\Event;


use Wamcar\User\BaseLikeVehicle;

class AbstractLikeVehicleEvent
{
    /** @var BaseLikeVehicle */
    private $likeVehicle;

    /**
     * AbstractLikeVehicleEvent constructor.
     * @param BaseLikeVehicle $likeVehicle
     */
    public function __construct(BaseLikeVehicle $likeVehicle)
    {
        $this->likeVehicle= $likeVehicle;
    }

    /**
     * @return BaseLikeVehicle
     */
    public function getLikeVehicle(): BaseLikeVehicle
    {
        return $this->likeVehicle;
    }
}