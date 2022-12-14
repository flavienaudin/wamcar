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
     * Indique si le profil est publiable. Conditions actuelles : aucune
     *
     * @return bool
     */
    public function isPublishable(): bool
    {
        return true;
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
     * @return int Number of user's vehicles
     */
    public function countVehicles(): int
    {
        return count($this->getVehicles());
    }

    /**
     * @param PersonalVehicle $personalVehicle
     */
    public function addPersonalVehicle(PersonalVehicle $personalVehicle)
    {
        $this->vehicles->add($personalVehicle);
        $personalVehicle->setOwner($this);
    }

    /**
     * @param PersonalVehicle $personalVehicle
     */
    public function removePersonalVehicle(PersonalVehicle $personalVehicle)
    {
        $this->vehicles->removeElement($personalVehicle);
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


    /**
     * Return the older date between the user project date (if not empty) and user's vehicle update dates. Or null if not vehicles and empty project
     * @return \DateTimeInterface|null
     */
    public function getLastSubmissionDate(): ?\DateTimeInterface
    {
        $projectDate = null;
        if ($this->getProject() != null && !$this->getProject()->isEmpty()) {
            $projectDate = $this->getProject()->getUpdatedAt();
        }
        $lastVehicleSubmission = null;
        /** @var PersonalVehicle $vehicle */
        foreach ($this->getVehicles() as $vehicle) {
            if ($vehicle->getDeletedAt() == null) {
                if ($lastVehicleSubmission == null || $vehicle->getUpdatedAt() > $lastVehicleSubmission) {
                    $lastVehicleSubmission = $vehicle->getUpdatedAt();
                }
            }
        }
        return $lastVehicleSubmission > $projectDate ? $lastVehicleSubmission : $projectDate;
    }
}