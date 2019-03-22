<?php

namespace AppBundle\Api\EntityBuilder;

use AppBundle\Services\Vehicle\CanBeProVehicle;
use AppBundle\Services\Vehicle\VehicleBuilder;
use Wamcar\Vehicle\Engine;
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
            new \DateTime($vehicleDTO->Date1Mec),
            !$vehicleDTO->Neuf,
            $vehicleDTO->Kilometrage,
            [],
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            $vehicleDTO->EquipementsSerieEtOption . "\n" . $vehicleDTO->Description,
            null,
            $vehicleDTO->PrixVenteTTC,
            null,
            null,
            null,
            $vehicleDTO->GarantieLibelle,
            null,
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
        $vehicle->setRegistrationDate(new \DateTime($vehicleDTO->Date1Mec));
        $vehicle->setIsUsed(!$vehicleDTO->Neuf);
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
        if (!$label) {
            return Transmission::TRANSMISSION_MANUAL();
        }

        $transmissionMatch = [
            'BVA' => Transmission::TRANSMISSION_AUTOMATIC(),
            'BVAS' => Transmission::TRANSMISSION_AUTOMATIC(),
            'BVM' => Transmission::TRANSMISSION_MANUAL(),
            'BVMS' => Transmission::TRANSMISSION_MANUAL(),
            'BVR' => Transmission::TRANSMISSION_AUTOMATIC(),
            'BVRD' => Transmission::TRANSMISSION_AUTOMATIC(),
            'CVT' => Transmission::TRANSMISSION_AUTOMATIC(),
            'E' => Transmission::TRANSMISSION_AUTOMATIC(),
            'I' => Transmission::TRANSMISSION_AUTOMATIC(),
            'N/D' => Transmission::TRANSMISSION_MANUAL()
        ];

        return $transmissionMatch[$label];
    }
}
