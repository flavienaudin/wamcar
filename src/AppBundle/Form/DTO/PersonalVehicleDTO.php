<?php

namespace AppBundle\Form\DTO;


class PersonalVehicleDTO extends VehicleDTO
{

    /**
     * VehicleDTO constructor.
     */
    public function __construct(string $registrationNumber = null)
    {
        parent::__construct($registrationNumber);
    }
}
