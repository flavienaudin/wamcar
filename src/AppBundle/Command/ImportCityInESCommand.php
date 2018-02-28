<?php

namespace AppBundle\Command;

use AppBundle\Elasticsearch\Type\IndexableCity;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportCityInESCommand extends BaseCommand
{

    /**
     * Configure command
     *
     */
    protected function configure()
    {
        $this
            ->setName('wamcar:populate:es-cities')
            ->setDescription('Populate ES with cities')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $objectIndexer = $this->getContainer()->get('Novaway\ElasticsearchClient\ObjectIndexer');
        $cityRepository = $this->getContainer()->get('AppBundle\Doctrine\Repository\DoctrineCityRepository');
        $indexableCityBuilder = $this->getContainer()->get('AppBundle\Elasticsearch\Builder\IndexableCityBuilder');

        $cities = $cityRepository->findAll();
        $progress = new ProgressBar($output, count($cities));

        foreach ($cities as $city) {
            $progress->advance();
            $objectIndexer->index($indexableCityBuilder->buildFromApplicationCity($city), IndexableCity::TYPE);
        }

        $progress->finish();

        $this->logCRLF();
        $this->log('success', 'Done !');
    }
}
