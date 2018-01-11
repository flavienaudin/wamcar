<?php

namespace Wamcar\Vehicle;

use Wamcar\Location\City;
use Wamcar\User\PersonalUser;

class PersonalVehicle extends BaseVehicle
{

    /** @var PersonalUser */
    private $owner;

    /**
     * @return PersonalUser
     */
    public function getOwner(): PersonalUser
    {
        return $this->owner;
    }

    /**
     * @param PersonalUser $owner
     * @return PersonalVehicle
     */
    public function setOwner(PersonalUser $owner): PersonalVehicle
    {
        $this->owner = $owner;
        return $this;
    }

    /**
     * @param PersonalUser|null $user
     * @return bool
     */
    public function canEditMe(PersonalUser $user = null)
    {
        return $this->getOwner() != null && $this->getOwner()->is($user);
    }

}
