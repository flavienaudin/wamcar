<?php


namespace Wamcar\User;


use AppBundle\Doctrine\Entity\AffinityDegree;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Wamcar\Garage\GarageProUser;
use Wamcar\Location\City;

class ProUser extends BaseUser
{
    const TYPE = 'pro';

    /** @var  string */
    protected $phonePro;
    /** @var  Collection */
    protected $garageMemberships;
    /** @var Collection $affinityDegrees */
    protected $affinityDegrees;

    /**
     * ProUser constructor.
     * @param string $email
     * @param string $firstName
     * @param string|null $name
     * @param City|null $city
     */
    public function __construct($email, $firstName, $name = null, $city = null)
    {
        parent::__construct($email, $firstName, $name, null, $city);
        $this->garageMemberships = new ArrayCollection();
        $this->affinityDegrees = new ArrayCollection();
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
     * {@inheritdoc}
     */
    public function hasGarage(): bool
    {
        return count($this->garageMemberships) > 0;

    }

    /**
     * @inheritdoc
     */
    public function getVehicles(): ?Collection
    {
        $vehicles = new ArrayCollection();
        /** @var GarageProUser $garageMembership */
        foreach ($this->garageMemberships as $garageMembership) {
            foreach ($garageMembership->getGarage()->getProVehicles() as $vehicle) {
                $vehicles->add($vehicle);
            }
        }
        return $vehicles;
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
    public function canSeeMyProfile(?BaseUser $user): bool
    {
        return true;
    }

    /**
     * @return Collection
     */
    public function getAffinityDegrees(): Collection
    {
        return $this->affinityDegrees;
    }

    /**
     * @param Collection $affinityDegrees
     */
    public function setAffinityDegrees(Collection $affinityDegrees): void
    {
        $this->affinityDegrees = $affinityDegrees;
    }

    /**
     * @param AffinityDegree $affinityDegree
     * @return ProUser
     */
    public function addAffinityDegree(AffinityDegree $affinityDegree): ProUser
    {
        $this->affinityDegrees->add($affinityDegree);
        return $this;
    }

    /**
     * @param AffinityDegree $affinityDegree
     * @return ProUser
     */
    public function removeAffinityDegree(AffinityDegree $affinityDegree): ProUser
    {
        $this->affinityDegrees->removeElement($affinityDegree);
        return $this;
    }
}
