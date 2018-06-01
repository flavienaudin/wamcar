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
            $vehicleDTO->isUsed(),
            $vehicleDTO->getMileage(),
            [],
            $vehicleDTO->isUsed()?$vehicleDTO->getSafetyTestDate():null,
            $vehicleDTO->isUsed()?$vehicleDTO->getSafetyTestState():null,
            $vehicleDTO->isUsed()?$vehicleDTO->getBodyState():null,
            $vehicleDTO->isUsed()?$vehicleDTO->getEngineState():null,
            $vehicleDTO->isUsed()?$vehicleDTO->getTyreState():null,
            $vehicleDTO->isUsed()?$vehicleDTO->getMaintenanceState():null,
            $vehicleDTO->isUsed()?$vehicleDTO->getTimingBeltState():null,
            $vehicleDTO->isUsed()?$vehicleDTO->isImported():null,
            $vehicleDTO->isUsed()?$vehicleDTO->isFirstHand():null,
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
        $vehicle->setRegistrationPlateNumber($vehicleDTO->getVehicleRegistration()->getPlateNumber());
        $vehicle->setRegistrationVin($vehicleDTO->getVehicleRegistration()->getVin());
        $vehicle->setIsUsed($vehicleDTO->isUsed());
        $vehicle->setMileage($vehicleDTO->getMileage());
        if($vehicle->isUsed()){
            $vehicle->setSafetyTestDate($vehicleDTO->getSafetyTestDate());
            $vehicle->setSafetyTestState($vehicleDTO->getSafetyTestState());
            $vehicle->setBodyState($vehicleDTO->getBodyState());
            $vehicle->setEngineState($vehicleDTO->getEngineState());
            $vehicle->setTyreState($vehicleDTO->getTyreState());
            $vehicle->setMaintenanceState($vehicleDTO->getMaintenanceState());
            $vehicle->setTimingBeltState($vehicleDTO->getTimingBeltState());
            $vehicle->setIsImported($vehicleDTO->isImported());
            $vehicle->setIsFirstHand($vehicleDTO->isFirstHand());
        }else{
            $vehicle->setSafetyTestDate(null);
            $vehicle->setSafetyTestState(null);
            $vehicle->setBodyState(null);
            $vehicle->setEngineState(null);
            $vehicle->setTyreState(null);
            $vehicle->setMaintenanceState(null);
            $vehicle->setTimingBeltState(null);
            $vehicle->setIsImported(null);
            $vehicle->setIsFirstHand(null);
        }
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
