<?php

namespace AppBundle\Command;

use AppBundle\Elasticsearch\Type\IndexableProVehicle;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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

        $objectIndexer = $this->getContainer()->get('Novaway\ElasticsearchClient\ObjectIndexer');
        $proVehicleRepository = $this->getContainer()->get('Wamcar\Vehicle\ProVehicleRepository');
        $indexableProVehicleBuilder = $this->getContainer()->get('AppBundle\Elasticsearch\Builder\IndexableProVehicleBuilder');

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
