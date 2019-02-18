<?php

namespace AppBundle\Services\Vehicle;


use AppBundle\Command\EntityBuilder\AutoManuelProVehicleBuilder;
use AppBundle\Exception\Vehicle\VehicleImportInvalidDataException;
use AppBundle\Exception\Vehicle\VehicleImportRGFailedException;
use function Composer\Autoload\includeFile;
use SimpleBus\Message\Bus\MessageBus;
use Wamcar\Garage\Garage;
use Wamcar\Garage\GarageRepository;
use Wamcar\Vehicle\Event\ProVehicleCreated;
use Wamcar\Vehicle\Event\ProVehicleRemoved;
use Wamcar\Vehicle\Event\ProVehicleUpdated;
use Wamcar\Vehicle\ProVehicle;
use Wamcar\Vehicle\ProVehicleRepository;

class VehicleImportService
{
    const ORIGIN_AUTOBONPLAN = "autobonplan";
    const ORIGIN_AUTOSMANUEL = "autosmanuel";
    const ORIGIN_EWIGO = "ewigo";

    const RESULT_STATUS_KEY = 'status';
    const RESULT_VEHICLE_KEY = 'vehicle';
    const RESULT_MOVED_KEY = 'moved';

    const RESULT_ERROR_KEY = 'errors';
    const RESULT_STATS_KEY = 'stats';
    const RESULT_NB_TREATED_ROWS_KEY = 'nbTreatedRows';
    const RESULT_NB_CREATED_VEHICLES_KEY = 'nbCreatedVehicles';
    const RESULT_NB_UPDATED_VEHICLES_KEY = 'nbUpdatedVehicles';
    const RESULT_NB_DELETED_VEHICLES_KEY = 'nbDeletedVehicles';
    const RESULT_NB_REJECTED_VEHICLES_KEY = 'nbRejectedVehicles';
    const RESULT_NB_MOVED_VEHICLES_KEY = 'nbMovedVehicles';

    const CREATION = 'creation';
    const UPDATE = 'update';

    /** @var GarageRepository */
    private $garageRepository;
    /** @var ProVehicleRepository */
    private $proVehicleRepository;
    /** @var MessageBus */
    private $eventBus;

    /**
     * VehicleImportService constructor.
     * @param GarageRepository $garageRepository
     * @param ProVehicleRepository $proVehicleRepository
     * @param MessageBus $eventBus
     */
    public function __construct(GarageRepository $garageRepository, ProVehicleRepository $proVehicleRepository, MessageBus $eventBus)
    {
        $this->garageRepository = $garageRepository;
        $this->proVehicleRepository = $proVehicleRepository;
        $this->eventBus = $eventBus;
    }

    /**
     * @param array $config
     * @param array $datas
     * @return array
     */
    public function importDataAutosManuel(array $config, array $datas){
        // Information data
        $idx = 0;
        $nbCreatedVehicles = 0;
        $nbUpdatedVehicles = 0;
        $nbDeletedVehicles = 0;
        $nbRejectedVehicles = 0;
        $nbMovedVehicles = 0;
        $errors = [];

        $vehicleTreatedReferences = [];
        $garages = [];
        foreach ($datas as $data) {
            if (is_array($data)) {
                try {
                    $result = $this->importProVehicleFromAutosManuelData($config, $data, $garages);
                    if($result[self::RESULT_STATUS_KEY] === self::CREATION){
                        $nbCreatedVehicles++;
                    }elseif($result[self::RESULT_STATUS_KEY] === self::UPDATE){
                        $nbUpdatedVehicles++;
                    }
                    if($result[self::RESULT_MOVED_KEY]){
                        $nbMovedVehicles++;
                    }
                    /** @var ProVehicle $proVehicle */
                    $proVehicle = $result[self::RESULT_VEHICLE_KEY];
                    $vehicleTreatedReferences[$proVehicle->getGarage()->getId()][] = $proVehicle->getReference();
                } catch (VehicleImportRGFailedException $e) {
                    if(!isset($errors[$e->getRgName()])){
                        $errors[$e->getRgName()] = [];
                    }
                    $errors[$e->getRgName()][$idx] = $e->getMessage();
                    $nbRejectedVehicles++;
                }
            } else {
                if(!isset($errors['invalidDataFormat'])) {
                    $errors['invalidDataFormat'] = [];
                }
                $errors['invalidDataFormat'][$idx] = 'Row is not an array';
                $nbRejectedVehicles++;
            }
            $idx++;
        }

        /** @var Garage $garage */
        foreach ($garages as $garage) {
            // Treat vehicle deletion
            $vehiclesToDelete = $this->proVehicleRepository->findByGarageAndExcludedReferences($garage, $vehicleTreatedReferences[$garage->getId()]);
            foreach ($vehiclesToDelete as $proVehicleToDelete) {
                $this->proVehicleRepository->remove($proVehicleToDelete);
                $this->eventBus->handle(new ProVehicleRemoved($proVehicleToDelete));
                $nbDeletedVehicles++;
            }
            $this->garageRepository->update($garage);
        }

        return [
            self::RESULT_ERROR_KEY => $errors,
            self::RESULT_STATS_KEY => [
                self::RESULT_NB_TREATED_ROWS_KEY => $idx,
                self::RESULT_NB_CREATED_VEHICLES_KEY => $nbCreatedVehicles,
                self::RESULT_NB_UPDATED_VEHICLES_KEY => $nbUpdatedVehicles,
                self::RESULT_NB_DELETED_VEHICLES_KEY => $nbDeletedVehicles,
                self::RESULT_NB_REJECTED_VEHICLES_KEY => $nbRejectedVehicles,
                self::RESULT_NB_MOVED_VEHICLES_KEY => $nbMovedVehicles
            ]
        ];
    }

    /**
     * @param array $config
     * @param array $data
     * @param array of Garage If we keep track of garages to limit DB calls, the already-known-garages can be given as array
     * @return array ['status' => {CREATION|UPDATE}, 'vehicle' => ProVehicle, 'moved' => {true|false}]
     * @throws VehicleImportRGFailedException
     */
    public function importProVehicleFromAutosManuelData(array $config, array $data, array &$garages = []): array
    {
        $origin = self::ORIGIN_AUTOSMANUEL;
        if ($data[AutoManuelProVehicleBuilder::FIELDNAME_RECIPIENT] != AutoManuelProVehicleBuilder::RECIPIENT_ACCEPTED_VALUE) {
            // RG-TRI-AM-Destination
            throw new VehicleImportRGFailedException('RG-TRI-AM-Destination', "Given value : ".$data[AutoManuelProVehicleBuilder::FIELDNAME_RECIPIENT]);
        }
        if (empty($data[AutoManuelProVehicleBuilder::FIELDNAME_INTERNET_PRICE]) && empty($data[AutoManuelProVehicleBuilder::FIELDNAME_MAIN_PRICE])) {
            // RG-TRI-AM-Prix
            throw new VehicleImportRGFailedException('RG-TRI-AM-Prix');
        }


        $garageCode = $data[AutoManuelProVehicleBuilder::FIELDNAME_GARAGE_CODE];
        if (!isset($garages[$garageCode])) {
            if (isset($config['garage'][$garageCode])) {
                $garage = $this->garageRepository->getByClientId($config['garage'][$garageCode]);
                if ($garage != null) {
                    $garages[$garageCode] = $garage;
                }
            }
        }
        if (isset($garages[$garageCode])) {
            $vehicleReference = $data[AutoManuelProVehicleBuilder::FIELDNAME_REFERENCE];
            $existingProVehicle = $this->proVehicleRepository->findByReference($vehicleReference);

            // Exiting vehicle is moved to another garage
            $wasMoved = false;
            if ($existingProVehicle != null && $existingProVehicle->getGarage() != $garages[$garageCode]) {
                $this->proVehicleRepository->remove($existingProVehicle);
                $this->eventBus->handle(new ProVehicleRemoved($existingProVehicle));
                $wasMoved = true;
                $existingProVehicle = null;
            }

            if ($origin == self::ORIGIN_AUTOSMANUEL) {
                // TODO
                $proVehicle = AutoManuelProVehicleBuilder::generateVehicleFromUsedData($existingProVehicle, $data, $garages[$garageCode]);
            } /*TODO remove :
                else {
                $proVehicle = AutoManuelProVehicleBuilder::generateVehicleFromNewData($existingProVehicle, $data, $garages[$garageCode]);
            }*/


            if ($existingProVehicle != null) {
                $this->proVehicleRepository->update($proVehicle);
                $this->eventBus->handle(new ProVehicleUpdated($proVehicle));
                return [self::RESULT_STATUS_KEY =>  self::UPDATE, self::RESULT_VEHICLE_KEY => $proVehicle, self::RESULT_MOVED_KEY => $wasMoved];
            } else {
                $this->proVehicleRepository->add($proVehicle);
                $this->eventBus->handle(new ProVehicleCreated($proVehicle));
                return [self::RESULT_STATUS_KEY =>  self::CREATION, self::RESULT_VEHICLE_KEY => $proVehicle, self::RESULT_MOVED_KEY => $wasMoved];
            }
        } else {
            throw new VehicleImportRGFailedException('RG-TRAIT-AM-Garage', sprintf("No garage configuration set for '%s', or API client Id not found", $garageCode));
        }
    }
}