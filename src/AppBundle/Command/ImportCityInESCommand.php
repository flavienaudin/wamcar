<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ImportCityInESCommand extends ContainerAwareCommand
{

    /**
     * Configure command
     *
     */
    protected function configure()
    {
        $this
            ->setName('wamcar:populate:es-cities')
            ->setDescription('Populate ES with cities');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $cityIndexer = $this->getContainer()->get('city.indexer');
        $indexableCityBuilder = $this->getContainer()->get('AppBundle\Elasticsearch\Builder\IndexableCityBuilder');
        $cityRepository = $this->getContainer()->get('AppBundle\Doctrine\Repository\DoctrineCityRepository');

        $cities = $cityRepository->findAll();
        $io->text('City reading');
        $cityDocuments = [];
        $io->progressStart(count($cities));
        foreach ($cities as $city) {
            $indexableCity = $indexableCityBuilder->buildFromApplicationCity($city);
            if ($indexableCity->shouldBeIndexed()) {
                $cityDocuments[] = $cityIndexer->buildDocument($indexableCity);
            }
            $io->progressAdvance();
        }
        $io->progressFinish();
        $io->newLine();

        $io->text('Indexing ' . count($cityDocuments) . ' cities');
        $cityIndexer->indexAllDocuments($cityDocuments, true);

        $io->success('Done !');
    }
}
