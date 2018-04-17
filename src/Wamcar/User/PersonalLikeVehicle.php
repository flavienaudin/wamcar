<?php

namespace Wamcar\User;


use Wamcar\Vehicle\PersonalVehicle;

class PersonalLikeVehicle extends BaseLikeVehicle
{

    /**
     * PersonalLikeVehicle constructor.
     * @param int $id
     * @param $value
     * @param BaseUser $user
     * @param PersonalVehicle $vehicle
     */
    public function __construct(int $id, $value, BaseUser $user, PersonalVehicle $vehicle)
    {
        $this->id = $id;
        $this->value = $value;
        $this->user = $user;
        $this->vehicle = $vehicle;
    }
}