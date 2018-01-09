<?php

namespace AppBundle\Form\DTO;


use Wamcar\User\ProjectVehicle;

class ProjectVehicleDTO
{
    /** @var int */
    public $id;
    /** @var string */
    public $make;
    /** @var string */
    public $model;
    /** @var  int */
    public $yearMax;
    /** @var  int */
    public $mileageMax;


    /**
     * @param ProjectVehicle $projectVehicle
     * @return self
     */
    public static function buildFromProjectVehicle(ProjectVehicle $projectVehicle): self
    {
        $dto = new self();
        $dto->id = $projectVehicle->getId();
        $dto->make = $projectVehicle->getMake();
        $dto->model = $projectVehicle->getModel();
        $dto->yearMax = $projectVehicle->getYearMax();
        $dto->mileageMax = $projectVehicle->getMileageMax();

        return $dto;
    }

    /**
     * @return array
     */
    public function retrieveFilter(): array
    {
        return [
            'make' => $this->make,
            'model' => $this->model
        ];
    }
}
