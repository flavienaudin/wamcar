<?php

namespace Wamcar\Vehicle;

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
        return ($this->getOwner() != null && $this->getOwner()->getName() != null) ?
            $this->getOwner()->getName() : 'unkonwn personal';
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
    public function canEditMe(BaseUser $user = null)
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

}
