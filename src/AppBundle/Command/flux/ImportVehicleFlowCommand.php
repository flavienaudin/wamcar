<?php

namespace AppBundle\Command\flux;


use AppBundle\Command\BaseCommand;
use AppBundle\Command\EntityBuilder\AutoManuelProVehicleBuilder;
use Doctrine\ORM\ORMException;
use SimpleBus\Message\Bus\MessageBus;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Wamcar\Garage\Garage;
use Wamcar\Garage\GarageRepository;
use Wamcar\Vehicle\Event\ProVehicleCreated;
use Wamcar\Vehicle\Event\ProVehicleRemoved;
use Wamcar\Vehicle\Event\ProVehicleUpdated;
use Wamcar\Vehicle\ProVehicle;
use Wamcar\Vehicle\ProVehicleRepository;

class ImportVehicleFlowCommand extends BaseCommand
{

    const SOURCE_AUTOBONPLAN = "autobonplan";
    const SOURCE_AUTOSMANUEL_VO = "autosmanuelVO";
    const SOURCE_AUTOSMANUEL_VN = "autosmanuelVN";

    /** @var GarageRepository */
    private $garageRepository;
    /** @var ProVehicleRepository */
    private $proVehicleRepository;
    /** @var MessageBus */
    private $eventBus;

    /**
     * Configure command
     *
     */
    protected function configure()
    {
        $this
            ->setName('wamcar:vehicleFlow:import')
            ->setDescription('Import vehicles from file')
            ->addOption('config',
                'c',
                InputOption::VALUE_REQUIRED,
                'Absolute path of ini configuration file of the flow to import'
            );
    }


    /**
     * Execute command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return void
     * @throws ORMException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Import command settings
        $this->output = $output;
        $this->eventBus = $this->getContainer()->get('event_bus');
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $this->garageRepository = $em->getRepository(Garage::class);
        $this->proVehicleRepository = $em->getRepository(ProVehicle::class);

        // Information data
        $nbUpdatedVehicles = 0;
        $nbCreatedVehicles = 0;
        $nbDeletedVehicles = 0;
        $nbRejectedVehicles = 0;

        // Execution configuration
        $configFile = $input->getOption('config');
        $config = parse_ini_file($configFile, false, INI_SCANNER_TYPED);

        $source = $config['source'];
        $this->log(BaseCommand::INFO, 'Source : ' . $source);
        $origin = $config['origin'];
        $this->log(BaseCommand::INFO, 'Origin  : ' . $origin);

        if ($source == "file") {
            $dataFile = $config[$source];
        } elseif ($source == "ftp") {
            // TODO download the file throught FTP connexion
            exit("FTP connection is not supported yet");
        }

        $this->log(BaseCommand::INFO, 'DataFile : ' . $dataFile);
        switch ($origin) {
            case self::SOURCE_AUTOBONPLAN:
                exit("AutoBonPlan is not supported yet");
                // $xmlElement = simplexml_load_file($dataFile);
                // $dataAsArray = ArrayToXMLConverter::revert($xmlElement->asXML());
                // dump($dataAsArray);
                // TODO
                // create new ProVehicle
                // setGarage
                break;
            case self::SOURCE_AUTOSMANUEL_VO:
            case self::SOURCE_AUTOSMANUEL_VN:
                $pictureDirectory = $config['pictureDirectory'];
                $garages = [];
                $currentRowNumero = 1;
                $vehicleTreatedReferences = [];

                $rejectedVehicles = [
                    'RG-TRI-AM-Destination' => [],
                    'RG-TRI-AM-Prix' => [],
                    'RG-TRAIT-AM-Garage' => [],
                    'WrongRowFormat' => []
                ];

                if (($handle = fopen($dataFile, "r")) !== FALSE) {
                    while (($data = fgetcsv($handle, 0, "|")) !== FALSE) {
                        if (is_array($data)) {
                            // add an element at the beginning of the $data array to fit documentation IDX of data
                            array_unshift($data, 0);

                            $validRow = true;

                            if ($origin == self::SOURCE_AUTOSMANUEL_VO) {
                                if ($data[AutoManuelProVehicleBuilder::IDX_VO_RECIPIENT] != AutoManuelProVehicleBuilder::VO_ACCEPTED_RECIPIENT_PARTICULIER) {
                                    // RG-TRI-AM-Destination
                                    $rejectedVehicles['RG-TRI-AM-Destination'][] = $currentRowNumero;
                                    $nbRejectedVehicles++;
                                    $validRow = false;
                                } elseif (empty($data[AutoManuelProVehicleBuilder::IDX_VO_INTERNET_PRICE]) && empty($data[AutoManuelProVehicleBuilder::IDX_VO_MAIN_PRICE])) {
                                    // RG-TRI-AM-Prix
                                    $rejectedVehicles['RG-TRI-AM-Prix'][] = $currentRowNumero;
                                    $nbRejectedVehicles++;
                                    $validRow = false;
                                }
                            } else {
                                if (empty($data[AutoManuelProVehicleBuilder::IDX_VN_INTERNET_PRICE]) && empty($data[AutoManuelProVehicleBuilder::IDX_VN_MAIN_PRICE])) {
                                    // RG-TRI-AM-Prix
                                    $rejectedVehicles['RG-TRI-AM-Prix'][] = $currentRowNumero;
                                    $nbRejectedVehicles++;
                                    $validRow = false;
                                }
                            }
                            if ($validRow) {
                                $garageCode = $data[$origin == self::SOURCE_AUTOSMANUEL_VO ? AutoManuelProVehicleBuilder::IDX_VO_GARAGE_CODE : AutoManuelProVehicleBuilder::IDX_VN_GARAGE_CODE];
                                if (!isset($garages[$garageCode])) {
                                    if (isset($config['garage'][$garageCode])) {
                                        $garage = $this->garageRepository->getByClientId($config['garage'][$garageCode]);
                                        if ($garage != null) {
                                            $garages[$garageCode] = $garage;
                                        }
                                    } else {
                                        $this->log(BaseCommand::ERROR, sprintf("There is no garage configuration set for %s", $garageCode));
                                    }
                                }
                                if (isset($garages[$garageCode])) {
                                    $vehicleReference = $data[$origin == self::SOURCE_AUTOSMANUEL_VO ? AutoManuelProVehicleBuilder::IDX_VO_REFERENCE : AutoManuelProVehicleBuilder::IDX_VN_NUM_POLICE];
                                    $existingProVehicle = $this->proVehicleRepository->findByReference($vehicleReference);

                                    // Exiting vehicle is moved to another garage
                                    if ($existingProVehicle != null && $existingProVehicle->getGarage() != $garages[$garageCode]) {
                                        $this->proVehicleRepository->remove($existingProVehicle);
                                        $this->eventBus->handle(new ProVehicleRemoved($existingProVehicle));
                                        $this->log(BaseCommand::INFO, sprintf('Vehicle ref. %s was moved from garage %d to garage %d',
                                            $vehicleReference, $existingProVehicle->getGarage()->getId(), $garages[$garageCode]));
                                        $existingProVehicle = null;
                                    }

                                    if ($origin == self::SOURCE_AUTOSMANUEL_VO) {
                                        $proVehicle = AutoManuelProVehicleBuilder::generateVehicleFromUsedData($existingProVehicle, $data, $garages[$garageCode], $pictureDirectory);
                                    } else {
                                        $proVehicle = AutoManuelProVehicleBuilder::generateVehicleFromNewData($existingProVehicle, $data, $garages[$garageCode], $pictureDirectory);
                                    }

                                    if (!$garages[$garageCode]->hasVehicle($proVehicle)) {
                                        // $garages[$garageCode]->addProVehicle($proVehicle);
                                        //  Done at the end of script only once by garage : Gardé pour vérifier qu'il n'y a pas de soucis
                                        // $this->garageRepository->update($vehicleGarage);
                                    }

                                    if ($existingProVehicle != null) {
                                        $this->proVehicleRepository->update($proVehicle);
                                        $this->eventBus->handle(new ProVehicleUpdated($proVehicle));
                                        $vehicleTreatedReferences[$garages[$garageCode]->getId()][] = $proVehicle->getReference();
                                        $nbUpdatedVehicles++;
                                    } else {
                                        $this->proVehicleRepository->add($proVehicle);
                                        $this->eventBus->handle(new ProVehicleCreated($proVehicle));
                                        $vehicleTreatedReferences[$garages[$garageCode]->getId()][] = $proVehicle->getReference();
                                        $nbCreatedVehicles++;
                                    }
                                } else {
                                    $rejectedVehicles['RG-TRAIT-AM-Garage'][$garageCode][] = $currentRowNumero;
                                    $nbRejectedVehicles++;
                                }
                            }
                        } else {
                            $nbRejectedVehicles++;
                            $rejectedVehicles['WrongRowFormat'][] = $currentRowNumero;
                        }
                        $currentRowNumero++;
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

                    fclose($handle);
                } else {
                    $this->log(BaseCommand::ERROR, sprintf("Access to data file (%s) is not allowed in read mode", $dataFile));
                }

                if (count($rejectedVehicles['RG-TRI-AM-Destination']) > 0) {
                    $this->log(BaseCommand::INFO, sprintf("RG-TRI-AM-Destination : (%d) rejected row(s) : %s", count($rejectedVehicles['RG-TRI-AM-Destination']), join(", ", $rejectedVehicles['RG-TRI-AM-Destination'])));
                }
                if (count($rejectedVehicles['RG-TRI-AM-Prix']) > 0) {
                    $this->log(BaseCommand::INFO, sprintf("RG-TRI-AM-Prix : (%d) rejected row(s) : %s", count($rejectedVehicles['RG-TRI-AM-Prix']), join(", ", $rejectedVehicles['RG-TRI-AM-Prix'])));
                }
                if (count($rejectedVehicles['RG-TRAIT-AM-Garage']) > 0) {
                    foreach ($rejectedVehicles['RG-TRAIT-AM-Garage'] as $codeGarage) {
                        $this->log(BaseCommand::INFO, sprintf("RG-TRAIT-AM-Garage : No garage set for %s : (%d) row(s) : %s", $garageCode, count($rejectedVehicles['RG-TRAIT-AM-Garage'][$codeGarage]),join(", ", $rejectedVehicles['RG-TRAIT-AM-Garage'][$codeGarage])));
                    }
                }
                if (count($rejectedVehicles['WrongRowFormat']) > 0) {
                    $this->log(BaseCommand::INFO, sprintf("Not '|'-separated-values rows (%d): %s", count($rejectedVehicles['WrongRowFormat']), join(", ", $rejectedVehicles['WrongRowFormat'])));
                }
                break;
            default:
                $this->log(BaseCommand::ERROR, sprintf("Source <%s> inconnue", $source));
        }
        $this->log(BaseCommand::INFO, '--------');
        $this->log(BaseCommand::INFO, 'Results:');
        $this->log(BaseCommand::INFO, '--------');
        $this->log(BaseCommand::INFO, 'Nb of rows : ' . ($currentRowNumero-1));
        $this->log(BaseCommand::INFO, 'Nb created vehicles : ' . $nbCreatedVehicles);
        $this->log(BaseCommand::INFO, 'Nb updated vehicles : ' . $nbUpdatedVehicles);
        $this->log(BaseCommand::INFO, 'Nb deleted vehicles : ' . $nbDeletedVehicles);
        $this->log(BaseCommand::INFO, 'Nb rejected vehicles : ' . $nbRejectedVehicles);
        $this->log(BaseCommand::INFO, '--------');
        $this->log(BaseCommand::SUCCESS, 'Done!');
    }
}