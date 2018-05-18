<?php


namespace Wamcar\User;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Wamcar\Vehicle\PersonalVehicle;
use Wamcar\Vehicle\Vehicle;

class PersonalUser extends BaseUser
{
    const TYPE = 'personal';

    /** @var Project|null */
    protected $project;
    /** @var Vehicle[]|array */
    protected $vehicles;

    /**
     * PersonalUser constructor.
     * @param string $email
     * @param string $firstName
     * @param string|null $name
     * @param PersonalVehicle $firstVehicle
     */
    public function __construct(string $email, $firstName, $name = null, PersonalVehicle $firstVehicle = null)
    {
        parent::__construct($email, $firstName, $name);

        $this->vehicles = new ArrayCollection();
        if($firstVehicle){
            $this->vehicles->add($firstVehicle);
        }
    }

    /**
     * Tell if the $user is authorized to see $this's profile
     * @param BaseUser|null $user
     * @return bool
     */
    public function canSeeMyProfile(?BaseUser $user): bool{
        return true;
    }

    /**
     * @return Collection
     */
    public function getVehicles(): Collection
    {
        return $this->vehicles;
    }

    /**
     * @param PersonalVehicle $personalVehicle
     */
    public function addPersonalVehicle(PersonalVehicle $personalVehicle)
    {
        $this->getVehicles()->add($personalVehicle);
        $personalVehicle->setOwner($this);
    }

    /**
     * @param PersonalVehicle $personalVehicle
     */
    public function removePersonalVehicle(PersonalVehicle $personalVehicle)
    {
        $this->getVehicles()->removeElement($personalVehicle);
        $personalVehicle->setOwner(null);
    }

    /**
     * @param PersonalVehicle $personalVehicle
     * @return bool
     */
    public function hasPersonalVehicle(PersonalVehicle $personalVehicle): bool
    {
        /** @var PersonalVehicle $existPersonalVehicle */
        foreach ($this->getVehicles() as $existPersonalVehicle) {
            if ($existPersonalVehicle->getId() === $personalVehicle->getId()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param BaseUser|null $user null if user not connected
     * @return bool
     */
    public function canSeeMyVehicles(BaseUser $user = null): bool
    {
        return true;
    }

    /**
     * @return null|Project
     */
    public function getProject(): ?Project
    {
        return $this->project;
    }

    /**
     * @param null|Project $project
     * @return PersonalUser
     */
    public function setProject(?Project $project): PersonalUser
    {
        $this->project = $project;
        return $this;
    }

}
