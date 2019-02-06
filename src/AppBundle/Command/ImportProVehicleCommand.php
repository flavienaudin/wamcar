<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ImportProVehicleCommand extends ContainerAwareCommand
{
    /**
     * Configure command
     *
     */
    protected function configure()
    {
        $this
            ->setName('wamcar:populate:pro_vehicle')
            ->setDescription('Populate the pro vehicle search with data from the pro vehicle entity');
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
        $io = new SymfonyStyle($input, $output);

        $proVehicleIndexer = $this->getContainer()->get('pro_vehicle.indexer');
        $proVehicleRepository = $this->getContainer()->get('Wamcar\Vehicle\ProVehicleRepository');
        $indexableProVehicleBuilder = $this->getContainer()->get('AppBundle\Elasticsearch\Builder\IndexableProVehicleBuilder');

        $vehicles = $proVehicleRepository->findAll();
        $proVehicleDocuments = [];
        $io->text('Pro Vehicle reading');
        $io->progressStart(count($vehicles));
        foreach ($vehicles as $vehicle) {
            $indexableProVehicle = $indexableProVehicleBuilder->buildFromVehicle($vehicle);
            if ($indexableProVehicle->shouldBeIndexed()) {
                $proVehicleDocuments[] = $proVehicleIndexer->buildDocument($indexableProVehicle);
            }
            $io->progressAdvance();
        }
        $io->progressFinish();
        $io->newLine();

        $io->text('Indexing ' . count($proVehicleDocuments) . ' pro vehicles');
        $proVehicleIndexer->indexAllDocuments($proVehicleDocuments, true);

        $io->success('Done !');
    }

}
