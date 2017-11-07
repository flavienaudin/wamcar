<?php


namespace Wamcar\User;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Wamcar\Garage\Garage;
use Wamcar\Garage\GarageProUser;

class ProUser extends BaseUser
{
    const TYPE = 'pro';

    /** @var string */
    protected $description;
    /** @var  string */
    protected $phonePro;
    /** @var  Collection */
    protected $garageMembers;

    /**
     * ProUser constructor.
     * @param string $email
     */
    public function __construct($email)
    {
        parent::__construct($email);
        $this->garageMembers = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @return string
     */
    public function getPhonePro(): ?string
    {
        return $this->phonePro;
    }

    /**
     * @param string $description
     */
    public function setDescription(?string $description)
    {
        $this->description = $description;
    }

    /**
     * @param string $phonePro
     */
    public function setPhonePro(?string $phonePro)
    {
        $this->phonePro = $phonePro;
    }

    /**
     * @return Collection
     */
    public function getGarageMembers(): Collection
    {
        return $this->garageMembers;
    }

    /**
     * @param Collection $members
     */
    public function setGarageMembers(Collection $members)
    {
        $this->garageMembers = $members;
    }

    /**
     * @param GarageProUser $member
     * @return ProUser
     */
    public function addGarageMember(GarageProUser $member): ProUser
    {
        $this->garageMembers->add($member);

        return $this;
    }

    /**
     * @param GarageProUser $member
     * @return ProUser
     */
    public function removeGarageMember(GarageProUser $member): ProUser
    {
        $this->garageMembers->removeElement($member);

        return $this;
    }

    /**
     * @return null|Garage
     */
    public function getGarage(): ?Garage
    {
        if (count($this->garageMembers) > 0) {
            return $this->garageMembers[0]->getGarage();
        }

        return null;
    }
}
