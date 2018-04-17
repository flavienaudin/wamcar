<?php

namespace Wamcar\User;


use Wamcar\Vehicle\ProVehicle;

class ProLikeVehicle extends BaseLikeVehicle
{
    /**
     * ProLikeVehicle constructor.
     * @param int $id
     * @param $value
     * @param BaseUser $user
     * @param ProVehicle $vehicle
     */
    public function __construct(int $id, $value, BaseUser $user, ProVehicle $vehicle)
    {
        $this->id = $id;
        $this->value = $value;
        $this->user = $user;
        $this->vehicle = $vehicle;
    }
}