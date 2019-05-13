<?php

namespace AppBundle\Command\flux;


use AppBundle\Command\BaseCommand;
use AppBundle\Services\Vehicle\VehicleImportService;
use Doctrine\ORM\ORMException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Wamcar\Garage\Garage;

class ImportVehicleFlowCommand extends BaseCommand
{
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

        if ($source === "file" || $source === "url") {
            $dataFile = $config[$source];
            $io->text('DataFile : ' . $dataFile);
        } elseif ($source === "ftp") {
            if (!isset($config['ftp']['host']) || !isset($config['ftp']['login']) || !isset($config['ftp']['password'])) {
                $io->error('Missing FTP settings : host, login or password');
                exit(-1);
            }
            $conn_id = ftp_connect($config['ftp']['host']);
            if (ftp_login($conn_id, $config['ftp']['login'], $config['ftp']['password'])) {
                ftp_pasv($conn_id, true);

                switch ($origin) {
                    case VehicleImportService::ORIGIN_POLEVO:
                        $importXMLFilesTempDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'polevo' . DIRECTORY_SEPARATOR;
                        if (file_exists($importXMLFilesTempDir) === FALSE) {
                            mkdir($importXMLFilesTempDir);
                        }

                        $garagesFlowFiles = [];
                        $garages = $vehicleImportService->getPoleVOGarage();
                        /** @var Garage $garage */
                        foreach ($garages as $garage) {
                            $garageFileName = date('Ymd_Hi') . '_export_garage_' . $garage->getPolevoId() . '.xml';
                            if (ftp_get($conn_id,
                                $importXMLFilesTempDir . $garageFileName,
                                ($config['ftp']['import_dir'] ?? DIRECTORY_SEPARATOR) . 'export_user_' . $garage->getPolevoId() . '.xml',
                                FTP_BINARY)) {
                                $io->text('File for garage PoleVO ' . $garage->getPolevoId() . ' was successfully downloaded in ' . $importXMLFilesTempDir . $garageFileName);
                                $garagesFlowFiles[$garage->getPolevoId()] = [
                                    'file' => $importXMLFilesTempDir . $garageFileName,
                                    'garage' => $garage
                                ];
                            } else {
                                $io->error('File for garage PoleVO ' . $garage->getPolevoId() . ' was not found on FTP server or other error');
                            }
                        }
                        break;
                    case VehicleImportService::ORIGIN_AUTOBONPLAN:
                        $importXMLFilesTempDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'autobonplan' . DIRECTORY_SEPARATOR;
                        if (file_exists($importXMLFilesTempDir) === FALSE) {
                            mkdir($importXMLFilesTempDir);
                        }
                        $flowFileName = date('Ymd_Hi') . '_wamcar_flux_products.xml';
                        if (ftp_get($conn_id, $importXMLFilesTempDir . $flowFileName, $config['ftp']['import_file'], FTP_BINARY)) {
                            $io->text('(AutoBonPlan) Flow file <'.$config['ftp']['import_file'].'> was successfully downloaded in ' . $importXMLFilesTempDir . $flowFileName);
                            $dataFile = $importXMLFilesTempDir . $flowFileName;
                        } else {
                            $io->error('(AutoBonPlan) Flow file <'.$config['ftp']['import_file'].'> was not found on FTP server or other error');
                            exit(-3);
                        }
                        break;
                    default:
                        ftp_close($conn_id);
                        $io->error("FTP connection is not supported yet for origin " . $origin);
                        exit(-2);
                }
            }
        }

        switch ($origin) {
            case VehicleImportService::ORIGIN_AUTOBONPLAN:
                if (file_exists($dataFile) === FALSE) {
                    $io->error(sprintf("Access to data file (%s) is not allowed in read mode", $dataFile));
                }else {
                    $io->text("Start at " . date(self::DATE_FORMAT));
                    $xmlElement = simplexml_load_file($dataFile);
                    $io->text("Datas read at " . date(self::DATE_FORMAT));
                    $result = $vehicleImportService->importDataAutoBonPlan($config, $xmlElement, $io);
                    $io->text("End at " . date(self::DATE_FORMAT));
                    $this->displayImportResult($io, $result);
                }
                break;
            case VehicleImportService::ORIGIN_AUTOSMANUEL:
                if ($source == "file" && file_exists($dataFile) === FALSE) {
                    $io->error(sprintf("Access to data file (%s) is not allowed in read mode", $dataFile));
                } elseif ($source == "url" && curl_init($dataFile) === FALSE) {
                    $io->error(sprintf("URL (%s) is not reachable", $dataFile));
                } else {
                    $io->text("Start at " . date(self::DATE_FORMAT));
                    $arrayJson = json_decode(file_get_contents($dataFile), true);
                    $io->text("Datas read at " . date(self::DATE_FORMAT));
                    $result = $vehicleImportService->importDataAutosManuel($config, $arrayJson, $io);
                    $io->text("End at " . date(self::DATE_FORMAT));
                    $this->displayImportResult($io, $result);
                }
                break;
            case VehicleImportService::ORIGIN_POLEVO:
                if ($source == "file" && file_exists($dataFile) === FALSE) {
                    $io->error(sprintf("Access to data file (%s) is not allowed in read mode", $dataFile));
                } elseif ($source == "ftp" && !isset($garagesFlowFiles)) {
                    $io->error(sprintf("FTP files could not be downloaded"));
                } else {
                    $io->text("Start at " . date(self::DATE_FORMAT));
                    if ($source === "file") {
                        $garagesFlowFiles = [];
                        $garageRepo = $this->getContainer()->get('AppBundle\Doctrine\Repository\DoctrineGarageRepository');
                        $garagesFlowFiles[$config['polevoId']] = [
                            'file' => $dataFile,
                            'garage' => $garageRepo->findOneBy(['polevoId' => $config['polevoId']])
                        ];
                    }
                    foreach ($garagesFlowFiles as $garageFlowData) {
                        $garageXMLFlow = simplexml_load_file($garageFlowData['file']);
                        $io->text("Datas read at " . date(self::DATE_FORMAT));
                        $result = $vehicleImportService->importDataPoleVo($garageXMLFlow, $garageFlowData['garage'], $io);
                        $io->text("End at " . date(self::DATE_FORMAT));
                        $this->displayImportResult($io, $result);
                    }
                }
                break;
            case VehicleImportService::ORIGIN_EWIGO:
                // TODO
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
        $io->success('Done at ' . date(self::DATE_FORMAT));
    }
}