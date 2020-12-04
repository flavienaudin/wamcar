<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Wamcar\User\PersonalUser;
use Wamcar\Vehicle\ProVehicle;

class ImportSearchItemCommand extends BaseCommand
{
    /**
     * Configure command
     *
     */
    protected function configure()
    {
        $this
            ->setName('wamcar:populate:search_item')
            ->setDescription('Populate the search_item index with data from pro vehicles and personal users (vehicle and project)');
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

        $searchItemEntityIndexer = $this->getContainer()->get('search_item.indexer');

        // Personal Users
        $io->text('[START] Personal User indexed as Search Item');
        $personalUserRepository = $this->getContainer()->get('AppBundle\Doctrine\Repository\DoctrinePersonalUserRepository');
        $indexableSearchItemBuilder = $this->getContainer()->get('AppBundle\Elasticsearch\Builder\IndexableSearchItemBuilder');
        $personalUsers = $personalUserRepository->findAll();
        $personaUserSearchItemDocumentsToIndex = [];
        $personaUserSearchItemIdsToDelete = [];
        $io->text('Personal User reading...');
        $io->progressStart(count($personalUsers));
        /** @var PersonalUser $user */
        foreach ($personalUsers as $user) {
            $personalUserSearchItemsByOperation = $indexableSearchItemBuilder->createSearchItemsFromPersonalUser($user);
            $personaUserSearchItemDocumentsToIndex = array_merge($personaUserSearchItemDocumentsToIndex, $personalUserSearchItemsByOperation['toIndex']);
            $personaUserSearchItemIdsToDelete = array_merge($personaUserSearchItemIdsToDelete, $personalUserSearchItemsByOperation['toDelete']);
            $io->progressAdvance();
        }
        $io->progressFinish();
        $io->text('Indexing ' . count($personaUserSearchItemDocumentsToIndex) . ' search items about personal users');
        $searchItemEntityIndexer->indexAllDocuments($personaUserSearchItemDocumentsToIndex, true);

        $io->text('Deleting ' . count($personaUserSearchItemIdsToDelete) . ' search items about personal users');
        if (count($personaUserSearchItemIdsToDelete) > 0) {
            $searchItemEntityIndexer->deleteByIds($personaUserSearchItemIdsToDelete);
        }
        $io->text('[END] Personal User indexed as Search Item');

        // Pro Vehicles
        $io->text('[START] Pro Vehicles indexed as Search Item');
        $proVehicleRepository = $this->getContainer()->get('Wamcar\Vehicle\ProVehicleRepository');
        $proVehicles = $proVehicleRepository->findAll();
        $proVehicleDocuments = [];
        $io->text('Pro Vehicle reading...');
        $io->progressStart(count($proVehicles));
        /** @var ProVehicle $proVehicle */
        foreach ($proVehicles as $proVehicle) {
            $indexableProVehicle = $indexableSearchItemBuilder->createSearchItemFromProVehicle($proVehicle);
            if ($indexableProVehicle->shouldBeIndexed()) {
                $proVehicleDocuments[] = $searchItemEntityIndexer->buildDocument($indexableProVehicle);
            }
            $io->progressAdvance();
        }
        $io->progressFinish();
        $io->text('Indexing ' . count($proVehicleDocuments) . ' search items about pro vehicles');
        $searchItemEntityIndexer->indexAllDocuments($proVehicleDocuments, true);
        $io->text('[END] Pro Vehicles indexed as Search Item');
        $io->success("Done at " . date(self::DATE_FORMAT));
    }

}
