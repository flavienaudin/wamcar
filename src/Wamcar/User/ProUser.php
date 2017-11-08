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
    protected $garageMemberships;

    /**
     * ProUser constructor.
     * @param string $email
     */
    public function __construct($email)
    {
        parent::__construct($email);
        $this->garageMemberships = new ArrayCollection();
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
    public function getGarageMemberships(): Collection
    {
        return $this->garageMemberships;
    }

    /**
     * @param Collection $members
     */
    public function setGarageMemberships(Collection $members)
    {
        $this->garageMemberships = $members;
    }

    /**
     * @param GarageProUser $member
     * @return ProUser
     */
    public function addGarageMembership(GarageProUser $member): ProUser
    {
        $this->garageMemberships->add($member);

        return $this;
    }

    /**
     * @param GarageProUser $member
     * @return ProUser
     */
    public function removeGarageMembership(GarageProUser $member): ProUser
    {
        $this->garageMemberships->removeElement($member);

        return $this;
    }

    /**
     * @return null|Garage
     */
    public function getGarage(): ?Garage
    {
        if (count($this->garageMemberships) > 0) {
            return $this->garageMemberships[0]->getGarage();
        }

        return null;
    }
}
