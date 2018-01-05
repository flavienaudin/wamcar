<?php

namespace AppBundle\Form\DTO;


use Wamcar\User\ProjectType;

class ProjectDTO
{
    /** @var ProjectType */
    public $type;
    /** @var int */
    public $budget;
    /** @var string */
    public $description;
    /** @var ProjectVehicleDTO[]|array */
    public $projectVehicles;

}
