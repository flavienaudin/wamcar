<?php

namespace Wamcar\User;

class ProjectVehicle
{
    /** @var  null|int */
    protected $id;
    /** @var  Project */
    protected $project;
    /** @var  string */
    protected $make;
    /** @var string */
    protected $model;
    /** @var  null|int */
    protected $yearMax;
    /** @var  null|int */
    protected $mileageMax;

    /**
     * ProjectVehicle constructor.
     * @param Project $project
     * @param int|null $id
     * @param string $make
     * @param string $model
     * @param int|null $yearMax
     * @param int|null $mileageMax
     */
    public function __construct(
        Project $project,
        int $id = null,
        string $make,
        string $model,
        int $yearMax = null,
        int $mileageMax = null
    )
    {
        $this->project = $project;
        $this->id = $id;
        $this->make = $make;
        $this->model = $model;
        $this->yearMax = $yearMax;
        $this->mileageMax = $mileageMax;
    }

    /**
     * @return null|int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Project
     */
    public function getProject(): Project
    {
        return $this->project;
    }

    /**
     * @return string
     */
    public function getMake(): string
    {
        return $this->make;
    }

    /**
     * @return string
     */
    public function getModel(): string
    {
        return $this->model;
    }

    /**
     * @return int|null
     */
    public function getYearMax(): ?int
    {
        return $this->yearMax;
    }

    /**
     * @return int|null
     */
    public function getMileageMax(): ?int
    {
        return $this->mileageMax;
    }

    /**
     * @param null|Project $project
     */
    public function setProject(?Project $project): void
    {
        $this->project = $project;
    }
}
