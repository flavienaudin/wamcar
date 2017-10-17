<?php

namespace AppBundle\Form\EntityBuilder;

use AppBundle\Doctrine\Entity\VehiclePicture;
use AppBundle\Form\DTO\VehicleDTO;
use Wamcar\Vehicle\Enum\MaintenanceState;
use Wamcar\Vehicle\Enum\SafetyTestState;
use Wamcar\Vehicle\PersonalVehicle;
use Wamcar\Vehicle\Vehicle;

class PersonalVehicleBuilder
{
    /**
     * @param VehicleDTO $vehicleDTO
     * @return Vehicle
     */
    public static function buildFromDTO(VehicleDTO $vehicleDTO): PersonalVehicle
    {
        dump($vehicleDTO);
        // TODO : implement real data when form is developed

        $vehicle = new PersonalVehicle(
            $vehicleDTO->getModelVersion(),
            $vehicleDTO->getTransmission(),
            null,
            new \DateTimeImmutable(),
            [],
            SafetyTestState::OK(),
            5,
            4,
            3,
            MaintenanceState::UP_TO_DATE_WITH_INVOICES(),
            false,
            false,
            false
        );

        foreach ($vehicleDTO->pictures as $pictureDTO) {
            if ($pictureDTO->file) {
                $picture = new VehiclePicture($vehicle, $pictureDTO->file, $pictureDTO->caption);
                $vehicle->addPicture($picture);
            }
        }

        return $vehicle;
    }
}
