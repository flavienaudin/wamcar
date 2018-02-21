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
            Registration::createFromVehicleRegistrationDTO($vehicleDTO->getVehicleRegistration()),
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

        foreach ($vehicleDTO->pictures as $index => $pictureDTO) {
            if ($pictureDTO && $pictureDTO->file) {
                $picture = new PersonalVehiclePicture($pictureDTO->id, $vehicle, $pictureDTO->file, $pictureDTO->caption, $index);
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

        foreach ($vehicleDTO->pictures as $index => $pictureDTO) {
            if ($pictureDTO && !$pictureDTO->isRemoved) {
                if ($pictureDTO->id && !$pictureDTO->file) {
                    $vehicle->editPictureCaption($pictureDTO->id, $pictureDTO->caption);
                } elseif ($pictureDTO->file) {
                    $picture = new PersonalVehiclePicture($pictureDTO->id, $vehicle, $pictureDTO->file, $pictureDTO->caption, $index);
                    $vehicle->addPicture($picture);
                }
            } elseif ($pictureDTO && $pictureDTO->isRemoved) {
                $vehicle->removePicture($pictureDTO->id);
            }
        }

        return $vehicle;
    }
}
