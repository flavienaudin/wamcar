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
    public static function buildFromDTO(CanBeProVehicle $vehicleDTO): ProVehicle
    {

        $vehicle = new ProVehicle(
            self::getModelVersion($vehicleDTO),
            self::getTransmission($vehicleDTO),
            $vehicleDTO->IdentifiantVehicule,
            new \DateTime($vehicleDTO->Annee . '-1-1 00:00:00'),
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
            null,
            $vehicleDTO->GarantieLibelle,
            null,
            null
        );

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
     * @param $vehicleDTO
     * @return Transmission
     */
    protected static function getTransmission($vehicleDTO): Transmission
    {
        return new Transmission($vehicleDTO->BoiteLibelle);
    }
}
