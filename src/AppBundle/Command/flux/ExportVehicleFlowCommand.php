<?php


namespace AppBundle\Command\flux;


use AppBundle\Command\BaseCommand;
use AppBundle\Services\Vehicle\VehicleExportService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ExportVehicleFlowCommand extends BaseCommand
{

    protected function configure()
    {
        $this
            ->setName('wamcar:vehicleFlow:export')
            ->setDescription('Export vehicles to file(s)')
            ->addOption('config',
                'c',
                InputOption::VALUE_REQUIRED,
                'Absolute path of ini configuration file of the flow to export'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $vehicleExportService = $this->getContainer()->get('AppBundle\Services\Vehicle\VehicleExportService');

        // Execution configuration
        $configFile = $input->getOption('config');
        $config = parse_ini_file($configFile, false, INI_SCANNER_TYPED);

        $origin = $config['origin'];
        switch ($origin) {
            case VehicleExportService::DEST_POLEVO:
                $results = $vehicleExportService->exportVehiclesToPoleVO();

                $io->text(sprintf("Export Reprises particuliers : %d reprises, location : %s", $results['reprises']['count'], $results['reprises']['fileDir'] . $results['reprises']['fileName']));
                $io->text(sprintf("Export Demandes particuliers : %d demandes, location : %s", $results['demandes']['count'], $results['demandes']['fileDir'] . $results['demandes']['fileName']));

                // Save/transfert files
                if ($config['export_location'] === 'local_dir') {
                    if (file_exists($config['local_dir']) === FALSE) {
                        mkdir($config['local_dir']);
                    }
                    rename($results['reprises']['fileDir'] . $results['reprises']['fileName'], $config['local_dir'] . $results['reprises']['fileName']);
                    rename($results['demandes']['fileDir'] . $results['demandes']['fileName'], $config['local_dir'] . $results['demandes']['fileName']);
                } elseif ($config['export_location'] === 'ftp') {
                    $conn_id = ftp_connect($config['ftp']['host']);
                    if (ftp_login($conn_id, $config['ftp']['login'], $config['ftp']['password'])) {
                        ftp_pasv($conn_id, true);
                        // "Reprises" file transfert
                        if (ftp_put($conn_id, $config['ftp']['export_dir'] . $results['reprises']['fileName'], $results['reprises']['fileDir'] . $results['reprises']['fileName'], FTP_BINARY)) {
                            $io->text("FTP transfert : fichier '" . $results['reprises']['fileName'] . "' ok");
                            unlink($results['reprises']['fileDir'] . $results['reprises']['fileName']);
                        } else {
                            $io->error("FTP transfert : ECHEC fichier '" . $results['reprises']['fileName']);
                        }
                        // "Demandes" file transfert
                        if (ftp_put($conn_id, $config['ftp']['export_dir'] . $results['demandes']['fileName'], $results['demandes']['fileDir'] . $results['demandes']['fileName'], FTP_BINARY)) {
                            $io->text("FTP transfert : fichier '" . $results['demandes']['fileName'] . "' ok");
                            unlink($results['reprises']['fileDir'] . $results['demandes']['fileName']);
                        } else {
                            $io->error("FTP transfert : ECHEC fichier '" . $results['demandes']['fileName']);
                        }
                    }
                    ftp_close($conn_id);
                }
                break;
            default:
                $io->warning('Destination "' . $origin . '" is not supported yet');
                exit(-1);
        }
        $io->success("Done at " . date(self::DATE_FORMAT));
    }

}