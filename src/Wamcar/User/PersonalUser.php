<?php


namespace Wamcar\User;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Wamcar\Vehicle\PersonalVehicle;

class PersonalUser extends BaseUser
{
    const TYPE = 'personal';

    /** @var ArrayCollection */
    protected $vehicles;

    /**
     * PersonalUser constructor.
     * @param string $email
     * @param PersonalVehicle $firstVehicle
     */
    public function __construct(string $email, PersonalVehicle $firstVehicle = null)
    {
        parent::__construct($email);

        $this->vehicles = new ArrayCollection();
        if($firstVehicle){
            $this->vehicles->add($firstVehicle);
        }
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
    }

    /**
     * @param PersonalVehicle $personalVehicle
     */
    public function removePersonalVehicle(PersonalVehicle $personalVehicle)
    {
        $this->getVehicles()->removeElement($personalVehicle);
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

}
