<?php

namespace AppBundle\Form\DTO;



final class PersonalVehicleDTO extends VehicleDTO
{
    /** @var RegistrationDTO */
    public $userRegistration;

    /**
     * VehicleDTO constructor.
     */
    public function __construct(string $registrationNumber = null)
    {
        parent::__construct($registrationNumber);
        $this->userRegistration = new RegistrationDTO();
    }
}
