<?php

namespace AppBundle\Form\DTO;


use Wamcar\Location\City;
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
    /** @var string */
    public $postalCode;
    /** @var string */
    public $cityName;
    /** @var string */
    public $latitude;
    /** @var string */
    public $longitude;
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

        if($project->getPersonalUser()->getCity() != null && !$project->getPersonalUser()->getCity()->isEmpty()){
            $dto->postalCode = $project->getPersonalUser()->getCity()->getPostalCode();
            $dto->cityName = $project->getPersonalUser()->getCity()->getName();
            $dto->latitude = $project->getPersonalUser()->getCity()->getLatitude();
            $dto->longitude = $project->getPersonalUser()->getCity()->getLongitude();
        }

        return $dto;
    }

    /**
     * @return null|City
     */
    public function getCity(): ?City
    {
        return ($this->postalCode && $this->cityName) ? new City($this->postalCode, $this->cityName, $this->latitude, $this->longitude) : null;
    }
}
