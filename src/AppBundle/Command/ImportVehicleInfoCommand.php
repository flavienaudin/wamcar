<?php

namespace AppBundle\Command;

use AppBundle\Elasticsearch\Type\VehicleInfo;
use League\Csv\Exception;
use League\Csv\Reader as CsvReader;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportVehicleInfoCommand extends BaseCommand
{
    /**
     * Configure command
     *
     */
    protected function configure()
    {
        $this
            ->setName('wamcar:populate:vehicle_info')
            ->setDescription('Populate the vehicle info table with data from an CSV file')
            ->addArgument(
                'file',
                InputArgument::OPTIONAL,
                'The path of the CSV file to load (default: fixture data)',
                __DIR__ . '/../../../database/fixtures/base_vehicule_20180228.csv'
            );
    }

    /**
     * Execute command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return void
     * @throws Exception If the Csv control character is not one character only.
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;

        $objectIndexer = $this->getContainer()->get('Novaway\ElasticsearchClient\ObjectIndexer');

        $csv = CsvReader::createFromPath($input->getArgument('file'), 'r');
        $csv->setDelimiter(';');
        $csv->setHeaderOffset(0);

        $nbRefused = 0;
        $progress = new ProgressBar($this->output, $csv->count());
        foreach ($csv->getRecords($csv->getHeader()) as $record) {
            $progress->advance();

            /*** Règles de tri ***/
            // TRI-1
            $bodyBlackList = ["Autobus/Autocar", "Camion basculant", "Camion plate-forme/Châssis", "Moto", "Semi-remorque"];
            if (in_array($record['tecdoc_carross'], $bodyBlackList)) {
                $nbRefused++;
                continue;
            }

            /*** Règles de traitement ***/
            // TRAIT-1
            $makeConstCodeToConstName = [
                121 => 'VOLKSWAGEN',
                609 => 'AC CARS'
            ];

            //TRAIT-2
            $energieName = [
                'moteur électrique' => 'Electrique'
            ];

            $vehicleInfo = new VehicleInfo(
                $record['tecdoc_ktypnr'],
                $record['tecdoc_constr'],
                isset($makeConstCodeToConstName[$record['tecdoc_constrcode']]) ? $makeConstCodeToConstName[$record['tecdoc_constrcode']] : $record['tecdoc_constr'],
                (int)$record['tecdoc_constrcode'],
                $record['tecdoc_model1'],
                (int)$record['tecdoc_modelcode'],
                $record['tecdoc_codemoteur'],
                $record['tecdoc_cyl'],
                new \DateTimeImmutable(sprintf('%s-%s-01 00:00', $record['tecdoc_anneedeb'], $record['tecdoc_moisdseb'])),
                $record['tecdoc_anneefin'] === '-' ? null : new \DateTimeImmutable(sprintf('%s-%s-01 00:00', $record['tecdoc_anneefin'], $record['tecdoc_moisfin'])),
                (float)$record['tecdoc_litr'],
                (int)$record['tecdoc_ccmtech'],
                (int)$record['tecdoc_kw'],
                (int)$record['tecdoc_cv'],
                $record['tecdoc_carross'],
                $record['tecdoc_propulsion'],
                $record['tecdoc_energie'],
                isset($energieName[$record['tecdoc_energie']]) ? $energieName[$record['tecdoc_energie']] : $record['tecdoc_energie'],
                (int)$record['tecdoc_nbcyl'],
                (int)$record['tecdoc_nbsoup']
            );

            $objectIndexer->index($vehicleInfo, VehicleInfo::TYPE);
        }

        $progress->finish();

        $this->logCRLF();
        $this->log('success', sprintf('Done ! (%d)', $nbRefused));
    }
}
