<?php


namespace Wamcar\User;


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
     * Return list of user's garages ordered by GoogleRating
     * @return array
     */
    public function getGaragesOrderByGoogleRating(): array
    {
        $orderedGrages = $this->getGarageMemberships()->toArray();
        uasort($orderedGrages, function ($gm1, $gm2){
            $g1gr = $gm1->getGarage()->getGoogleRating() ?? -1;
            $g2gr = $gm2->getGarage()->getGoogleRating() ?? -1;
           if($g1gr == $g2gr){
               return 0;
           }
           return $g1gr < $g2gr ? 1 : -1;
        });
        return $orderedGrages;
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
}
