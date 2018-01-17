<?php

namespace AppBundle\Command;

use AppBundle\Elasticsearch\Type\IndexablePersonalVehicle;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportPersonalVehicleCommand extends BaseCommand
{
    /**
     * Configure command
     *
     */
    protected function configure()
    {
        $this
            ->setName('wamcar:populate:personal_vehicle')
            ->setDescription('Populate the personal vehicle search with data from the personal vehicle entity');
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
        $personalVehicleRepository = $this->getContainer()->get('Wamcar\Vehicle\PersonalVehicleRepository');
        $indexablePersonalVehicleBuilder = $this->getContainer()->get('AppBundle\Elasticsearch\Builder\IndexablePersonalVehicleBuilder');

        $vehicles = $personalVehicleRepository->findAll();
        $progress = new ProgressBar($output, count($vehicles));

        foreach ($vehicles as $vehicle) {
            $progress->advance();
            $objectIndexer->index($indexablePersonalVehicleBuilder->buildFromVehicle($vehicle), IndexablePersonalVehicle::TYPE);
        }

        $progress->finish();

        $this->logCRLF();
        $this->log('success', 'Done !');
    }

}
