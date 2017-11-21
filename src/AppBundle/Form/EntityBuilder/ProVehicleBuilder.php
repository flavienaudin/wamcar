<?php

namespace AppBundle\Form\EntityBuilder;

use AppBundle\Doctrine\Entity\VehiclePicture;
use AppBundle\Form\DTO\ProVehicleDTO;
use Wamcar\Vehicle\ProVehicle;
use Wamcar\Vehicle\Registration;

class ProVehicleBuilder
{
    /**
     * @param ProVehicleDTO $vehicleDTO
     * @return ProVehicle
     */
    public static function buildFromDTO(ProVehicleDTO $vehicleDTO): ProVehicle
    {
        $vehicle = new ProVehicle(
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
            $vehicleDTO->getPrice(),
            $vehicleDTO->getCatalogPrice(),
            $vehicleDTO->getDiscount(),
            $vehicleDTO->getGuarantee(),
            $vehicleDTO->isRefunded(),
            $vehicleDTO->getOtherGuarantee(),
            $vehicleDTO->getAdditionalServices(),
            $vehicleDTO->getReference()
        );

        foreach ($vehicleDTO->pictures as $pictureDTO) {
            if ($pictureDTO && $pictureDTO->file) {
                $picture = new VehiclePicture($vehicle, $pictureDTO->file, $pictureDTO->caption);
                $vehicle->addPicture($picture);
            }
        }

        return $vehicle;
    }
}
