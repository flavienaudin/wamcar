<?php

namespace Wamcar\User;

class Project
{
    /** @var  PersonalUser */
    protected $personalUser;
    /** @var  bool */
    protected $isFleet;
    /** @var null|int */
    protected $budget;
    /** @var  null|string */
    protected $description;
    /** @var ProjectVehicle[]|array */
    protected $projectVehicles;

    /**
     * Project constructor.
     * @param PersonalUser $personalUser
     * @param bool $isFleet
     * @param int|null $budget
     * @param string|null $description
     * @param array $projectVehicles
     */
    public function __construct(
        PersonalUser $personalUser,
        bool $isFleet = false,
        int $budget = null,
        string $description = null,
        array $projectVehicles = []
    )
    {
        $this->personalUser = $personalUser;
        $this->isFleet = $isFleet;
        $this->budget = $budget;
        $this->description = $description;
        $this->projectVehicles = $projectVehicles;
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
    public function getProjectVehicles(): array
    {
        return $this->projectVehicles;
    }
}
