<?php

namespace AppBundle\Form\DTO;


use Wamcar\Vehicle\PersonalVehicle;

class PersonalVehicleDTO extends VehicleDTO
{

    /**
     * VehicleDTO constructor.
     */
    public function __construct(string $registrationNumber = null)
    {
        parent::__construct($registrationNumber);
    }


    /**
     * @param PersonalVehicle $vehicle
     * @return ProVehicleDTO
     */
    public static function buildFromPersonalVehicle(PersonalVehicle $vehicle): self
    {
        $dto = new self();
        $dto->information = VehicleInformationDTO::buildFromInformation(
            $vehicle->getMake(),
            $vehicle->getModelName(),
            $vehicle->getModelVersionName(),
            $vehicle->getEngineName(),
            $vehicle->getTransmission(),
            $vehicle->getFuelName()
        );

        $dto->specifics = VehicleSpecificsDTO::buildFromSpecifics(
            $vehicle->getRegistrationDate(),
            $vehicle->getMileage(),
            $vehicle->getisTimingBeltChanged(),
            $vehicle->getSafetyTestDate(),
            $vehicle->getSafetyTestState(),
            $vehicle->getBodyState(),
            $vehicle->getEngineState(),
            $vehicle->getTyreState(),
            $vehicle->getMaintenanceState(),
            $vehicle->getisImported(),
            $vehicle->getisFirstHand(),
            $vehicle->getAdditionalInformation(),
            $vehicle->getPostalCode(),
            $vehicle->getCityName(),
            $vehicle->getLatitude(),
            $vehicle->getLongitude()
        );

        foreach ($vehicle->getPictures() as $picture) {
            $dto->pictures[] = VehiclePictureDTO::buildFromPicture($picture);
        }

        return $dto;
    }
}
