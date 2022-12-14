<?php

namespace Wamcar\User;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Gedmo\SoftDeleteable\Traits\SoftDeleteable;

class Project
{
    use SoftDeleteable;

    /** @var  int */
    protected $id;
    /** @var  PersonalUser */
    protected $personalUser;
    /** @var  bool */
    protected $isFleet = false;
    /** @var null|int */
    protected $budget;
    /** @var  null|string */
    protected $description;
    /** @var ProjectVehicle[]|Collection */
    protected $projectVehicles;
    /** @var \DateTimeInterface */
    protected $createdAt;
    /** @var \DateTimeInterface */
    protected $updatedAt;

    /**
     * Project constructor.
     * @param PersonalUser $personalUser
     */
    public function __construct(
        PersonalUser $personalUser
    )
    {
        $this->personalUser = $personalUser;
        $this->projectVehicles = new ArrayCollection();
    }

    public function isEmpty():bool
    {
        return empty($this->description) && $this->budget == null && $this->projectVehicles->isEmpty();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return bool
     */
    public function isFleet(): bool
    {
        return $this->isFleet;
    }

    /**
     * @return int|null
     */
    public function getBudget(): ?int
    {
        return $this->budget;
    }

    /**
     * @return null|string
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @return PersonalUser
     */
    public function getPersonalUser(): PersonalUser
    {
        return $this->personalUser;
    }

    /**
     * @return array|ProjectVehicle[]
     */
    public function getProjectVehicles()
    {
        return $this->projectVehicles;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getUpdatedAt(): \DateTimeInterface
    {
        return $this->updatedAt;
    }

    /**
     * @param bool $isFleet
     */
    public function setIsFleet(bool $isFleet): void
    {
        $this->isFleet = $isFleet;
    }

    /**
     * @param int|null $budget
     */
    public function setBudget(?int $budget): void
    {
        $this->budget = $budget;
    }

    /**
     * @param null|string $description
     */
    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    /**
     * @param $projectVehicles
     * @return Collection|ProjectVehicle[]
     */
    public function setProjectVehicles($projectVehicles)
    {
        $keepIdProjectVehicles = [];

        /** @var ProjectVehicle $projectVehicle */
        foreach ($projectVehicles as $key => $projectVehicle) {
            if ($projectVehicle->getId()) {
                $keepIdProjectVehicles[$projectVehicle->getId()] = $projectVehicle;
                unset($projectVehicles[$key]);
            }
        }

        foreach ($this->getProjectVehicles() as $projectVehicle) {
            if (!isset($keepIdProjectVehicles[$projectVehicle->getId()])) {
                $this->removeProjectVehicle($projectVehicle);
            } else {
                $this->updateProjectVehicle($projectVehicle, $keepIdProjectVehicles[$projectVehicle->getId()]);
            }
        }

        /** @var ProjectVehicle $projectVehicle */
        foreach ($projectVehicles as $projectVehicle) {
            $this->addProjectVehicle($projectVehicle);
        }

        return $this->projectVehicles;
    }

    /**
     * @param ProjectVehicle $projectVehicle
     * @return Collection|ProjectVehicle[]
     */
    public function addProjectVehicle(ProjectVehicle $projectVehicle)
    {
        if (!$this->projectVehicles->contains($projectVehicle)) {
            $this->projectVehicles->add($projectVehicle);
        }

        return $this->projectVehicles;
    }

    /**
     * @param ProjectVehicle $projectVehicle
     * @return Collection|ProjectVehicle[]
     */
    public function removeProjectVehicle(ProjectVehicle $projectVehicle)
    {
        if ($this->projectVehicles->contains($projectVehicle)) {
            $this->projectVehicles->removeElement($projectVehicle);
        }

        return $this->projectVehicles;
    }

    /**
     * @param ProjectVehicle $projectVehicle
     * @param ProjectVehicle $updateProjectVehicle
     * @return ProjectVehicle
     */
    public function updateProjectVehicle(ProjectVehicle $projectVehicle, ProjectVehicle $updateProjectVehicle)
    {
        $projectVehicle->setMake($updateProjectVehicle->getMake());
        $projectVehicle->setModel($updateProjectVehicle->getModel());
        $projectVehicle->setYearMin($updateProjectVehicle->getYearMin());
        $projectVehicle->setMileageMax($updateProjectVehicle->getMileageMax());

        return $projectVehicle;
    }
}
