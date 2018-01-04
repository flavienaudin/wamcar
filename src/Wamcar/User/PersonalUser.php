<?php


namespace Wamcar\User;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Wamcar\Vehicle\PersonalVehicle;

class PersonalUser extends BaseUser
{
    const TYPE = 'personal';

    /** @var ArrayCollection */
    protected $personalVehicles;

    /**
     * PersonalUser constructor.
     * @param string $email
     * @param PersonalVehicle $firstVehicle
     */
    public function __construct(string $email, PersonalVehicle $firstVehicle = null)
    {
        parent::__construct($email);

        $this->personalVehicles = new ArrayCollection();
        if($firstVehicle){
            $this->personalVehicles->add($firstVehicle);
        }
    }


    /**
     * @return Collection
     */
    public function getPersonalVehicles(): Collection
    {
        return $this->personalVehicles;
    }

    /**
     * @param PersonalVehicle $personalVehicle
     * @return PersonalUser
     */
    public function addPersonalVehicle(PersonalVehicle $personalVehicle): PersonalUser
    {
        $this->getPersonalVehicles()->add($personalVehicle);

        return $this;
    }

    /**
     * @param PersonalVehicle $personalVehicle
     * @return PersonalUser
     */
    public function removePersonalVehicle(PersonalVehicle $personalVehicle): PersonalUser
    {
        $this->getPersonalVehicles()->removeElement($personalVehicle);

        return $this;
    }

    /**
     * @param PersonalVehicle $personalVehicle
     * @return bool
     */
    public function hasPersonalVehicle(PersonalVehicle $personalVehicle): bool
    {
        /** @var PersonalVehicle $existPersonalVehicle */
        foreach ($this->getPersonalVehicles() as $existPersonalVehicle) {
            if ($existPersonalVehicle->getId() === $personalVehicle->getId()) {
                return true;
            }
        }

        return false;
    }


}
