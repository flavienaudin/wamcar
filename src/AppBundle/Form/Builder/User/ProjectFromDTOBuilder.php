<?php

namespace AppBundle\Form\Builder\User;


use AppBundle\Form\DTO\ProjectDTO;
use Wamcar\User\PersonalUser;
use Wamcar\User\Project;
use Wamcar\User\ProjectType;
use Wamcar\User\ProjectVehicle;

class ProjectFromDTOBuilder
{
    /**
     * @param ProjectDTO $dto
     * @param PersonalUser $user
     * @return Project
     */
    public static function buildFromDTO($dto, PersonalUser $user): Project
    {
        if (!$dto instanceof ProjectDTO) {
            throw new \InvalidArgumentException(
                sprintf(
                    "ProjectFromDTOBuilder::buildFromDTO expects $dto argument to be an instance of '%s', '%s' given"),
                ProjectDTO::class,
                get_class($dto)
            );
        }

        $project = $user->getProject() ?: new Project($user);
        $project->setBudget($dto->budget);
        $project->setDescription($dto->description);
        $project->setIsFleet($dto->isFleet);

        $projectVehicles =[];
        foreach ($dto->projectVehicles as $projectVehicleDTO) {
            $projectVehicles[] = new ProjectVehicle($project,
                $projectVehicleDTO->id,
                $projectVehicleDTO->make,
                $projectVehicleDTO->model,
                $projectVehicleDTO->yearMax,
                $projectVehicleDTO->mileageMax
            );
        }
        $project->setProjectVehicles($projectVehicles);

        return $project;
    }
}
