<?php

namespace AppBundle\Form\EntityBuilder;

use AppBundle\Doctrine\Entity\ProVehiclePicture;
use AppBundle\Form\DTO\VehiclePictureDTO;
use AppBundle\Services\Vehicle\CanBeProVehicle;
use AppBundle\Services\Vehicle\VehicleBuilder;
use Wamcar\Vehicle\ProVehicle;
use Wamcar\Vehicle\Registration;

class ProVehicleBuilder implements VehicleBuilder
{

    /**
     * @param CanBeProVehicle $vehicleDTO
     * @return ProVehicle
     */
    public static function newVehicleFromDTO(CanBeProVehicle $vehicleDTO): ProVehicle
    {
        $vehicle = new ProVehicle(
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
            $vehicleDTO->getCity(),
            $vehicleDTO->getPrice(),
            $vehicleDTO->getCatalogPrice(),
            $vehicleDTO->getDiscount(),
            $vehicleDTO->getGuarantee(),
            $vehicleDTO->getOtherGuarantee(),
            $vehicleDTO->getFunding(),
            $vehicleDTO->getOtherFunding(),
            $vehicleDTO->getAdditionalServices(),
            $vehicleDTO->getReference()
        );

        foreach ($vehicleDTO->pictures as $index => $pictureDTO) {
            if ($pictureDTO && $pictureDTO->file) {
                $picture = new ProVehiclePicture(null, $vehicle, $pictureDTO->file, $pictureDTO->caption, $index);
                $vehicle->addPicture($picture);
            }
        }

        return $vehicle;
    }

    /**
     * @param CanBeProVehicle $vehicleDTO
     * @param ProVehicle $vehicle
     * @return ProVehicle
     */
    public static function editVehicleFromDTO(CanBeProVehicle $vehicleDTO, ProVehicle $vehicle): ProVehicle
    {
        $vehicle->setModelVersion($vehicleDTO->getModelVersion());
        $vehicle->setRegistration(Registration::createFromVehicleRegistrationDTO($vehicleDTO->getVehicleRegistration()));
        $vehicle->setTransmission($vehicleDTO->getTransmission());
        $vehicle->setRegistrationDate($vehicleDTO->getRegistrationDate());
        $vehicle->setIsUsed($vehicleDTO->isUsed());
        $vehicle->setMileage($vehicleDTO->getMileage());
        if($vehicle->isUsed()) {
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
        $vehicle->setPrice($vehicleDTO->getPrice());
        $vehicle->setCatalogPrice($vehicleDTO->getCatalogPrice());
        $vehicle->setDiscount($vehicleDTO->getDiscount());
        $vehicle->setGuarantee($vehicleDTO->getGuarantee());
        $vehicle->setOtherGuarantee($vehicleDTO->getOtherGuarantee());
        $vehicle->setFunding($vehicleDTO->getFunding());
        $vehicle->setOtherFunding($vehicleDTO->getOtherFunding());
        $vehicle->setOtherGuarantee($vehicleDTO->getOtherGuarantee());
        $vehicle->setAdditionalServices($vehicleDTO->getAdditionalServices());
        $vehicle->setReference($vehicleDTO->getReference());

        /** @var VehiclePictureDTO $pictureDTO */
        foreach ($vehicleDTO->pictures as $index => $pictureDTO) {
            if ($pictureDTO && !$pictureDTO->isRemoved) {
                if ($pictureDTO->id && !$pictureDTO->file) {
                    $vehicle->editPictureCaption($pictureDTO->id, $pictureDTO->caption);
                } elseif ($pictureDTO->file) {
                    $picture = new ProVehiclePicture($pictureDTO->id, $vehicle, $pictureDTO->file, $pictureDTO->caption, $index);
                    $vehicle->addPicture($picture);
                }
            } else if ($pictureDTO && $pictureDTO->isRemoved) {
                $vehicle->removePicture($pictureDTO->id);
            }
        }

        return $vehicle;
    }
}
