<?php

namespace AppBundle\Form\DTO;


use Wamcar\User\Project;

class ProjectDTO
{
    /** @var bool */
    public $isFleet;
    /** @var int */
    public $budget;
    /** @var string */
    public $description;
    /** @var ProjectVehicleDTO[]|array */
    public $projectVehicles;
    /**
     * @param Project $project
     * @return self
     */
    public static function buildFromProject(Project $project): self
    {
        $dto = new self();
        $dto->isFleet = $project->isFleet();
        $dto->description = $project->getDescription();
        $dto->budget = $project->getBudget();

        foreach ($project->getProjectVehicles() as $projectVehicle) {
            $dto->projectVehicles[] = ProjectVehicleDTO::buildFromProjectVehicle($projectVehicle);
        }

        return $dto;
    }
}