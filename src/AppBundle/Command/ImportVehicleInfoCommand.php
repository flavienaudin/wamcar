<?php

namespace AppBundle\Command;

use AppBundle\Elasticsearch\Type\VehicleInfo;
use League\Csv\Reader as CsvReader;
use Novaway\ElasticsearchClient\Aggregation\Aggregation;
use Novaway\ElasticsearchClient\Filter\TermFilter;
use Novaway\ElasticsearchClient\Query\QueryBuilder;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Wamcar\Vehicle\Fuel;

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
                __DIR__ . '/../../../database/fixtures/base_vehicule_short.csv'
            );
    }

    /**
     * Execute command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;

        $objectIndexer = $this->getContainer()->get('Novaway\ElasticsearchClient\ObjectIndexer');

        $csv = CsvReader::createFromPath($input->getArgument('file'), 'r');
        $csv->setDelimiter(';');
        $csv->setHeaderOffset(0);

        $progress = new ProgressBar($output, $csv->count());
        foreach ($csv->getRecords($csv->getHeader()) as $record) {
            $progress->advance();
            $vehicleInfo = new VehicleInfo(
                $record['tecdoc_ktypnr'],
                $record['tecdoc_constr'],
                $record['tecdoc_model1'],
                $record['finition'],
                $record['tecdoc_codemoteur'],
                $record['tecdoc_cyl'],
                new \DateTimeImmutable(sprintf('%s-%s-01 00:00', $record['tecdoc_anneedeb'], $record['tecdoc_moisdseb'])),
                $record['tecdoc_anneefin'] === '-' ? null : new \DateTimeImmutable(sprintf('%s-%s-01 00:00', $record['tecdoc_anneefin'], $record['tecdoc_moisfin'])),
                (float)$record['tecdoc_litr'],
                (int)$record['tecdoc_cv'],
                $record['tecdoc_carross'],
                $record['tecdoc_propulsion'],
                $record['tecdoc_energie'],
                (int)$record['tecdoc_nbcyl'],
                (int)$record['tecdoc_nbsoup']
            );

            $objectIndexer->index($vehicleInfo, VehicleInfo::TYPE);
        }

        $progress->finish();

        $this->logCRLF();
        $this->log('success', 'Done !');
    }

}
