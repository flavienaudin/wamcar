<?php

namespace Wamcar\User;

class ProjectVehicle
{
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
     * @param string $make
     * @param string $model
     * @param int|null $yearMax
     * @param int|null $mileageMax
     */
    public function __construct(
        Project $project,
        string $make,
        string $model,
        int $yearMax = null,
        int $mileageMax = null
    )
    {
        $this->project = $project;
        $this->make = $make;
        $this->model = $model;
        $this->yearMax = $yearMax;
        $this->mileageMax = $mileageMax;
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
}
