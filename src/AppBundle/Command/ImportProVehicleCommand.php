<?php

namespace AppBundle\Command;

use AppBundle\Elasticsearch\Type\IndexableProVehicle;
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

class ImportProVehicleCommand extends BaseCommand
{
    /**
     * Configure command
     *
     */
    protected function configure()
    {
        $this
            ->setName('wamcar:populate:pro_vehicle')
            ->setDescription('Populate the pro vehicle search with data from the pro vehicle entity')
            ;
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

        $index = $this->getContainer()->get('Novaway\ElasticsearchClient\Index');
        $objectIndexer = $this->getContainer()->get('Novaway\ElasticsearchClient\ObjectIndexer');
        $proVehicleRepository = $this->getContainer()->get('Wamcar\Vehicle\VehicleRepository');
        $indexableProVehicleBuilder = $this->getContainer()->get('AppBundle\Elasticsearch\Builder\IndexableProVehicleBuilder');

        $searchProVehicles = $index->search(['index' => 'wamcar_index_dev', 'type' => IndexableProVehicle::TYPE]);
        $progress = new ProgressBar($output, $searchProVehicles->totalHits());
        foreach ($searchProVehicles->hits() as $searchProVehicle) {
            $progress->advance();
            $index->delete(['id' => $searchProVehicle['id'], 'index' => 'wamcar_index_dev', 'type' => IndexableProVehicle::TYPE]);
        }
        $progress->finish();

        $this->log('info', 'Reload !');

        $vehicles = $proVehicleRepository->findAll();
        $progress = new ProgressBar($output, count($vehicles));

        foreach ($vehicles as $vehicle) {
            $progress->advance();
            $objectIndexer->index($indexableProVehicleBuilder->buildFromVehicle($vehicle), IndexableProVehicle::TYPE);

        }

        $progress->finish();

        $this->logCRLF();
        $this->log('success', 'Done !');
    }

}
