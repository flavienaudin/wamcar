<?php


namespace Wamcar\User;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Wamcar\Location\City;
use Wamcar\User\Enum\PersonalOrientationChoices;
use Wamcar\Vehicle\BaseVehicle;
use Wamcar\Vehicle\PersonalVehicle;

class PersonalUser extends BaseUser
{
    const TYPE = 'personal';

    /** @var null|PersonalOrientationChoices */
    protected $orientation;
    /** @var  string (json) */
    protected $contactAvailabilities;
    /** @var Project|null */
    protected $project;
    /** @var Collection */
    protected $vehicles;

    /**
     * PersonalUser constructor.
     * @param string $email
     * @param string $firstName
     * @param string|null $name
     * @param PersonalVehicle|null $firstVehicle
     * @param City|null $city
     */
    public function __construct(string $email, $firstName, $name = null, PersonalVehicle $firstVehicle = null, City $city = null)
    {
        parent::__construct($email, $firstName, $name, null, $city);

        $this->vehicles = new ArrayCollection();
        if ($firstVehicle) {
            $this->vehicles->add($firstVehicle);
        }
    }

    /**
     * @return PersonalOrientationChoices|null
     */
    public function getOrientation()
    {
        return $this->orientation;
    }

    /**
     * @param ?PersonalOrientationChoices $orientation
     */
    public function setOrientation(?PersonalOrientationChoices $orientation): void
    {
        $this->orientation = $orientation;
    }

    /**
     * @return null|string
     */
    public function getContactAvailabilities(): ?string
    {
        return $this->contactAvailabilities;
    }

    /**
     * @param string $contactAvailabilities
     */
    public function setContactAvailabilities(string $contactAvailabilities): void
    {
        $this->contactAvailabilities = $contactAvailabilities;
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
     * @inheritdoc
     */
    public function getVehicles(?int $limit = 0, BaseVehicle $excludedVehicle = null): Collection
    {
        $criteria = Criteria::create();
        if ($excludedVehicle != null) {
            $criteria->where(Criteria::expr()->neq('id', $excludedVehicle->getId()));
        }
        if ($limit > 0) {
            $criteria->setMaxResults($limit);
        }
        return $this->vehicles->matching($criteria);
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