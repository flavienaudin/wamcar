<?php

namespace AppBundle\Form\DTO;


final class UserRegistrationPersonalVehicleDTO extends PersonalVehicleDTO
{
    /** @var RegistrationDTO */
    public $userRegistration;

    /**
     * VehicleDTO constructor.
     */
    public function __construct(string $registrationNumber = null, string $date1erCir = null, string $vin = null)
    {
        parent::__construct($registrationNumber, $date1erCir, $vin);
        $this->userRegistration = new RegistrationDTO();
        $this->pictures = self::initFormPictureVehicle();
    }
}
