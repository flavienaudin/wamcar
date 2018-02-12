<?php

namespace Wamcar\Vehicle;

use Wamcar\Location\City;
use Wamcar\User\BaseUser;
use Wamcar\User\PersonalUser;
use Wamcar\User\Project;
use Wamcar\User\ProUser;

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
     * @return PersonalUser
     */
    public function getOwnerName(): string
    {
        return ($this->getOwner() != null && $this->getOwner()->getFullName() != null) ?
            $this->getOwner()->getFullName() : 'unkonwn personal';
    }

    /**
     * @return null|Project
     */
    public function getOwnerProject(): ?Project
    {
        return $this->getOwner() != null ? $this->getOwner()->getProject() : null;
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
     * @param BaseUser|null $user
     * @return bool
     */
    public function canEditMe(BaseUser $user = null): bool
    {
        return $this->getOwner() != null && $this->getOwner()->is($user);
    }

    /**
     * @return \DateTimeInterface
     */
    public function getUpdatedAt(): \DateTimeInterface
    {
        return $this->updatedAt;
    }

    /**
     * @param BaseUser|null $user
     * @return bool
     */
    public function canSeeMe(BaseUser $user = null)
    {
        return $this->getOwner() != null && ($this->getOwner()->is($user) || $user instanceof ProUser);
    }

    /**
     * @return City
     */
    protected function getCity(): ?City
    {
        return $this->city;
    }

    /**
     * @return PersonalUser
     */
    public function getSeller(): PersonalUser
    {
        return $this->getOwner();
    }

}
