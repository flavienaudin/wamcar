<?php

namespace AppBundle\Form\DTO;

use Wamcar\Vehicle\Engine;
use Wamcar\Vehicle\Enum\MaintenanceState;
use Wamcar\Vehicle\Enum\SafetyTestDate;
use Wamcar\Vehicle\Enum\SafetyTestState;
use Wamcar\Vehicle\Enum\Transmission;
use Wamcar\Vehicle\Fuel;
use Wamcar\Vehicle\Make;
use Wamcar\Vehicle\Model;
use Wamcar\Vehicle\ModelVersion;

class PersonalVehicleDTO extends VehicleDTO
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
