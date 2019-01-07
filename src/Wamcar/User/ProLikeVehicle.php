<?php

namespace Wamcar\User;


use Wamcar\Vehicle\ProVehicle;

class ProLikeVehicle extends BaseLikeVehicle
{
    /**
     * ProLikeVehicle constructor.
     * @param BaseUser $user
     * @param ProVehicle $vehicle
     * @param $value
     */
    public function __construct(BaseUser $user, ProVehicle $vehicle,$value)
    {
        $this->user = $user;
        $this->vehicle = $vehicle;
        $this->value = $value;
    }
}