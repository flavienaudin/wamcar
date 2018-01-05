<?php

namespace AppBundle\Form\DTO;


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

    public function __construct()
    {
        $this->projectVehicles = [new ProjectVehicleDTO()];
    }
}
