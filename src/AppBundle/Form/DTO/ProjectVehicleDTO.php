<?php

namespace AppBundle\Form\DTO;


class ProjectVehicleDTO
{
    /** @var string */
    public $make;
    /** @var string */
    public $model;
    /** @var  int */
    protected $yearMax;
    /** @var  int */
    protected $mileageMax;
}
