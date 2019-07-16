<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

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
        $io = new SymfonyStyle($input, $output);

        $personalVehicleIndexer = $this->getContainer()->get('personal_vehicle.indexer');
        $personalVehicleRepository = $this->getContainer()->get('Wamcar\Vehicle\PersonalVehicleRepository');
        $indexablePersonalVehicleBuilder = $this->getContainer()->get('AppBundle\Elasticsearch\Builder\IndexablePersonalVehicleBuilder');

        $vehicles = $personalVehicleRepository->findAll();
        $personalVehicleDocuments = [];
        $io->text('Personal Vehicle reading');
        $io->progressStart(count($vehicles));
        foreach ($vehicles as $vehicle) {
            $indexablePersonalVehicle = $indexablePersonalVehicleBuilder->buildFromVehicle($vehicle);
            if ($indexablePersonalVehicle->shouldBeIndexed()) {
                $personalVehicleDocuments[] = $personalVehicleIndexer->buildDocument($indexablePersonalVehicle);
            }
            $io->progressAdvance();
        }
        $io->progressFinish();

        $io->text('Indexing ' . count($personalVehicleDocuments) . ' personal vehicles');
        $personalVehicleIndexer->indexAllDocuments($personalVehicleDocuments, true);

        $io->success("Done at " . date(self::DATE_FORMAT));
    }

}
