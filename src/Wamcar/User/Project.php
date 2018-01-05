<?php

namespace Wamcar\User;

class Project
{
    /** @var  int */
    protected $id;
    /** @var  PersonalUser */
    protected $personalUser;
    /** @var  null|ProjectType */
    protected $type;
    /** @var null|int */
    protected $budget;
    /** @var  null|string */
    protected $description;
    /** @var ProjectVehicle[]|array */
    protected $projectVehicles;

    /**
     * Project constructor.
     * @param PersonalUser $personalUser
     * @param ProjectType|null $type
     * @param int|null $budget
     * @param string|null $description
     * @param array $projectVehicles
     */
    public function __construct(
        PersonalUser $personalUser,
        ProjectType $type = null,
        int $budget = null,
        string $description = null,
        array $projectVehicles = []
    )
    {
        $this->personalUser = $personalUser;
        $this->type = $type;
        $this->budget = $budget;
        $this->description = $description;
        $this->projectVehicles = $projectVehicles;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return null|ProjectType
     */
    public function getType(): ?ProjectType
    {
        return $this->type;
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
