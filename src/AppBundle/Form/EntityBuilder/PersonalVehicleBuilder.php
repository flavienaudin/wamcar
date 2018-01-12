<?php

namespace AppBundle\Form\EntityBuilder;

use AppBundle\Doctrine\Entity\PersonalVehiclePicture;
use AppBundle\Form\DTO\PersonalVehicleDTO;
use Wamcar\Vehicle\PersonalVehicle;
use Wamcar\Vehicle\Registration;

class PersonalVehicleBuilder
{

    /**
     * @param PersonalVehicleDTO $vehicleDTO
     * @return PersonalVehicle
     */
    public static function buildFromDTO(PersonalVehicleDTO $vehicleDTO): PersonalVehicle
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

    /**
     * @param PersonalVehicleDTO $vehicleDTO
     * @param PersonalVehicle $vehicle
     * @return PersonalVehicle
     */
    public static function editVehicleFromDTO(PersonalVehicleDTO $vehicleDTO, PersonalVehicle $vehicle): PersonalVehicle
    {
        $vehicle->setModelVersion($vehicleDTO->getModelVersion());
        $vehicle->setTransmission($vehicleDTO->getTransmission());
        $vehicle->setRegistrationDate($vehicleDTO->getRegistrationDate());
        $vehicle->setMileage($vehicleDTO->getMileage());
        $vehicle->setSafetyTestDate($vehicleDTO->getSafetyTestDate());
        $vehicle->setSafetyTestState($vehicleDTO->getSafetyTestState());
        $vehicle->setBodyState($vehicleDTO->getBodyState());
        $vehicle->setEngineState($vehicleDTO->getEngineState());
        $vehicle->setTyreState($vehicleDTO->getTyreState());
        $vehicle->setMaintenanceState($vehicleDTO->getMaintenanceState());
        $vehicle->setIsTimingBeltChanged($vehicleDTO->isTimingBeltChanged());
        $vehicle->setIsImported($vehicleDTO->isImported());
        $vehicle->setIsFirstHand($vehicleDTO->isFirstHand());
        $vehicle->setAdditionalInformation($vehicleDTO->getAdditionalInformation());
        $vehicle->setCity($vehicleDTO->getCity());

        foreach ($vehicleDTO->pictures as $pictureDTO) {
            if ($pictureDTO && $pictureDTO->file) {
                $picture = new PersonalVehiclePicture($vehicle, $pictureDTO->file, $pictureDTO->caption);
                $vehicle->addPicture($picture);
            }
        }

        return $vehicle;
    }
}
