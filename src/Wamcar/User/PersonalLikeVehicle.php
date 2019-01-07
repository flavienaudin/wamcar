<?php

namespace Wamcar\User;


use Wamcar\Vehicle\PersonalVehicle;

class PersonalLikeVehicle extends BaseLikeVehicle
{

    /**
     * PersonalLikeVehicle constructor.
     * @param BaseUser $user
     * @param PersonalVehicle $vehicle
     * @param $value
     */
    public function __construct(BaseUser $user, PersonalVehicle $vehicle, $value)
    {
        $this->user = $user;
        $this->vehicle = $vehicle;
        $this->value = $value;
    }
}