<?php


namespace Wamcar\User;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Wamcar\Garage\Garage;
use Wamcar\Garage\GarageProUser;

class ProUser extends BaseUser
{
    const TYPE = 'pro';

    /** @var  string */
    protected $phonePro;
    /** @var  Collection */
    protected $garageMemberships;

    /**
     * ProUser constructor.
     * @param string $email
     * @param string $firstName
     * @param string|null $name
     */
    public function __construct($email, $firstName, $name = null)
    {
        parent::__construct($email, $firstName, $name);
        $this->garageMemberships = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function getPhonePro(): ?string
    {
        return $this->phonePro;
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
     * @inheritdoc
     */
    public function getGarage(): ?Garage
    {
        if (count($this->garageMemberships) > 0) {
            return $this->garageMemberships[0]->getGarage();
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function getVehicles(): ?Collection
    {
        if($this->getGarage() != null) {
            return $this->getGarage()->getProVehicles();
        } else {
            return null;
        }

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
     * Tell if the $user is authorized to see $this's profile
     * @param BaseUser|null $user
     * @return bool
     */
    public function canSeeMyProfile(?BaseUser $user): bool{
        return true;
    }
}
