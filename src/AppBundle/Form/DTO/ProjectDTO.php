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

}
