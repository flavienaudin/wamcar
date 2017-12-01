<?php

namespace AppBundle\Form\EntityBuilder;

use AppBundle\Doctrine\Entity\PersonalVehiclePicture;
use AppBundle\Form\DTO\VehicleDTO;
use Wamcar\Vehicle\Enum\MaintenanceState;
use Wamcar\Vehicle\Enum\SafetyTestState;
use Wamcar\Vehicle\PersonalVehicle;
use Wamcar\Vehicle\Registration;
use Wamcar\Vehicle\Vehicle;

class PersonalVehicleBuilder
{

    /**
     * @param VehicleDTO $vehicleDTO
     * @return PersonalVehicle
     */
    public static function buildFromDTO(VehicleDTO $vehicleDTO): PersonalVehicle
    {
        $vehicle = new PersonalVehicle(
            $vehicleDTO->getModelVersion(),
            $vehicleDTO->getTransmission(),
            $vehicleDTO->registrationNumber ? Registration::createFromPlateNumber($vehicleDTO->registrationNumber) : null,
            $vehicleDTO->getRegistrationDate(),
            $vehicleDTO->getMileage(),
            [],
            $vehicleDTO->getSafetyTestDate(),
            $vehicleDTO->getSafetyTestState(),
            $vehicleDTO->getBodyState(),
            $vehicleDTO->getEngineState(),
            $vehicleDTO->getTyreState(),
            $vehicleDTO->getMaintenanceState(),
            $vehicleDTO->isTimingBeltChanged(),
            $vehicleDTO->isImported(),
            $vehicleDTO->isFirstHand(),
            $vehicleDTO->getAdditionalInformation(),
            $vehicleDTO->getCity()
        );

        foreach ($vehicleDTO->pictures as $pictureDTO) {
            if ($pictureDTO && $pictureDTO->file) {
                $picture = new PersonalVehiclePicture($vehicle, $pictureDTO->file, $pictureDTO->caption);
                $vehicle->addPicture($picture);
            }
        }

        return $vehicle;
    }
}
