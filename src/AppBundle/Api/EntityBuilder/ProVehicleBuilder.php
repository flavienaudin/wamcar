<?php

namespace AppBundle\Api\EntityBuilder;

use AppBundle\Services\Vehicle\CanBeProVehicle;
use AppBundle\Services\Vehicle\VehicleBuilder;
use Wamcar\Vehicle\Engine;
use Wamcar\Vehicle\Enum\MaintenanceState;
use Wamcar\Vehicle\Enum\SafetyTestDate;
use Wamcar\Vehicle\Enum\SafetyTestState;
use Wamcar\Vehicle\Enum\Transmission;
use Wamcar\Vehicle\Fuel;
use Wamcar\Vehicle\Make;
use Wamcar\Vehicle\Model;
use Wamcar\Vehicle\ModelVersion;
use Wamcar\Vehicle\ProVehicle;

class ProVehicleBuilder implements VehicleBuilder
{
    /**
     * @param CanBeProVehicle $vehicleDTO
     * @return ProVehicle
     */
    public static function newVehicleFromDTO(CanBeProVehicle $vehicleDTO): ProVehicle
    {

        $vehicle = new ProVehicle(
            self::getModelVersion($vehicleDTO),
            self::getTransmissionMatch($vehicleDTO->BoiteLibelle),
            null,
            new \DateTime($vehicleDTO->Annee . '-1-1 00:00:00'),
            $vehicleDTO->Kilometrage,
            [],
            SafetyTestDate::UNKNOWN(),
            SafetyTestState::UNKNOWN(),
            3,
            null,
            null,
            MaintenanceState::UNKNOWN(),
            null,
            null,
            null,
            $vehicleDTO->EquipementsSerieEtOption . "\n" . $vehicleDTO->Description,
            null,
            $vehicleDTO->PrixVenteTTC,
            null,
            null,
            null,
            null,
            $vehicleDTO->GarantieLibelle,
            null,
            null,
            $vehicleDTO->IdentifiantVehicule
        );

        return $vehicle;
    }

    /**
     * @param CanBeProVehicle $vehicleDTO
     * @param ProVehicle $vehicle
     * @return ProVehicle
     */
    public static function editVehicleFromDTO(CanBeProVehicle $vehicleDTO, ProVehicle $vehicle): ProVehicle
    {
        $vehicle->setModelVersion(self::getModelVersion($vehicleDTO));
        $vehicle->setTransmission(self::getTransmissionMatch($vehicleDTO->BoiteLibelle));
        $vehicle->setRegistrationDate(new \DateTime($vehicleDTO->Annee . '-1-1 00:00:00'));
        $vehicle->setMileage($vehicleDTO->Kilometrage);
        $vehicle->setAdditionalInformation($vehicleDTO->EquipementsSerieEtOption . PHP_EOL . $vehicleDTO->Description);
        $vehicle->setPrice($vehicleDTO->PrixVenteTTC);
        $vehicle->setOtherGuarantee($vehicleDTO->GarantieLibelle);

        return $vehicle;
    }

    /**
     * @param $vehicleDTO
     * @return ModelVersion
     */
    protected static function getModelVersion($vehicleDTO): ModelVersion
    {
        return new ModelVersion($vehicleDTO->Version, self::getModel($vehicleDTO), self::getEngine($vehicleDTO));
    }

    /**
     * @param $vehicleDTO
     * @return Model
     */
    protected static function getModel($vehicleDTO): Model
    {
        return new Model($vehicleDTO->Modele, self::getMake($vehicleDTO));
    }

    /**
     * @param $vehicleDTO
     * @return Engine
     */
    protected static function getEngine($vehicleDTO): Engine
    {
        return new Engine($vehicleDTO->Motorisation, self::getFuel($vehicleDTO));
    }


    /**
     * @param $vehicleDTO
     * @return Make
     */
    protected static function getMake($vehicleDTO): Make
    {
        return new Make($vehicleDTO->Marque);
    }

    /**
     * @param $vehicleDTO
     * @return Fuel
     */
    protected static function getFuel($vehicleDTO): Fuel
    {
        return new Fuel($vehicleDTO->Energie);
    }

    /**
     * @param null|string $label
     * @return Transmission
     */
    protected static function getTransmissionMatch(?string $label): Transmission
    {
        if(!$label) {
            return Transmission::MANUAL();
        }

        $transmissionMatch = [
            'BVA' => Transmission::AUTOMATIC(),
            'BVAS' => Transmission::AUTOMATIC(),
            'BVM' => Transmission::MANUAL(),
            'BVMS' => Transmission::MANUAL(),
            'BVR' => Transmission::AUTOMATIC(),
            'BVRD' => Transmission::AUTOMATIC(),
            'CVT' => Transmission::AUTOMATIC(),
            'E' => Transmission::AUTOMATIC(),
            'I' => Transmission::AUTOMATIC(),
            'N/D' => Transmission::MANUAL()
        ];

        return $transmissionMatch[$label];
    }
}
