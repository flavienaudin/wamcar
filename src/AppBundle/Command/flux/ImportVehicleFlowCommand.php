<?php

namespace AppBundle\Command\flux;


use AppBundle\Command\BaseCommand;
use AppBundle\Exception\Vehicle\VehicleImportInvalidDataException;
use AppBundle\Exception\Vehicle\VehicleImportRGFailedException;
use AppBundle\Services\Vehicle\VehicleImportService;
use Doctrine\ORM\ORMException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Wamcar\Garage\Garage;
use Wamcar\Vehicle\Event\ProVehicleRemoved;
use Wamcar\Vehicle\ProVehicle;

class ImportVehicleFlowCommand extends BaseCommand
{


    /** Configure command */
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
        $io = new SymfonyStyle($input, $output);
        $vehicleImportService = $this->getContainer()->get('AppBundle\Services\Vehicle\VehicleImportService');

        // Execution configuration
        $configFile = $input->getOption('config');
        $config = parse_ini_file($configFile, false, INI_SCANNER_TYPED);

        $source = $config['source'];
        $io->text('Source : ' . $source);
        $origin = $config['origin'];
        $io->text('Origin  : ' . $origin);

        if ($source == "file" || $source == "url") {
            $dataFile = $config[$source];
        } elseif ($source == "ftp") {
            // TODO download the file throught FTP connexion
            exit("FTP connection is not supported yet");
        }

        $io->text('DataFile : ' . $dataFile);
        switch ($origin) {
            case VehicleImportService::ORIGIN_AUTOBONPLAN:
                exit("AutoBonPlan is not supported yet");
                // $xmlElement = simplexml_load_file($dataFile);
                // $dataAsArray = ArrayToXMLConverter::revert($xmlElement->asXML());
                // dump($dataAsArray);
                // TODO
                // create new ProVehicle
                // setGarage
                break;
            case VehicleImportService::ORIGIN_AUTOSMANUEL:

                if ($source == "file" && file_exists($dataFile) === FALSE) {
                    $io->error(sprintf("Access to data file (%s) is not allowed in read mode", $dataFile));
                } elseif ($source == "url" && curl_init($dataFile) === FALSE) {
                    $io->error(sprintf("URL (%s) is not reachable", $dataFile));
                } else {
                    $io->text("Start at " . date("H:i:s"));
                    $arrayJson = json_decode(file_get_contents($dataFile), true);
                    $io->text("Datas read at " . date("H:i:s"));
                    $result = $vehicleImportService->importDataAutosManuel($config, $arrayJson, $io);
                    $io->text("End at " . date("H:i:s"));
                    $this->displayImportResult($io, $result);
                }
                break;
            case VehicleImportService::ORIGIN_EWIGO:
                // TODO
                break;
            default:
                $io->error(sprintf("Origin <%s> inconnue", $origin));
        }
    }

    private function displayImportResult(SymfonyStyle $io, array $result)
    {
        foreach ($result[VehicleImportService::RESULT_ERROR_KEY] as $rgName => $errorData) {
            $io->text(sprintf("%s : (%d) rejected row(s) :", $rgName, count($errorData)));
            $errorMessages = [];
            foreach ($errorData as $idx => $message) {
                $errorMessages[] = [$idx, $message];
            }
            $io->table(['idx', 'message'], $errorMessages);
        }
        $io->table(['Results:', ''], [
            ['Nb of rows : ', $result[VehicleImportService::RESULT_STATS_KEY][VehicleImportService::RESULT_NB_TREATED_ROWS_KEY]],
            ['Nb created vehicles : ', $result[VehicleImportService::RESULT_STATS_KEY][VehicleImportService::RESULT_NB_CREATED_VEHICLES_KEY]],
            ['Nb updated vehicles : ', $result[VehicleImportService::RESULT_STATS_KEY][VehicleImportService::RESULT_NB_UPDATED_VEHICLES_KEY]],
            ['Nb deleted vehicles : ', $result[VehicleImportService::RESULT_STATS_KEY][VehicleImportService::RESULT_NB_DELETED_VEHICLES_KEY]],
            ['Nb rejected vehicles : ', $result[VehicleImportService::RESULT_STATS_KEY][VehicleImportService::RESULT_NB_REJECTED_VEHICLES_KEY]],
            ['Nb moved vehicles : ', $result[VehicleImportService::RESULT_STATS_KEY][VehicleImportService::RESULT_NB_MOVED_VEHICLES_KEY]]
        ]);
        $io->success('Done!');
    }
}