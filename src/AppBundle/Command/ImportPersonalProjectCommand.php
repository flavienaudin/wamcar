<?php

namespace AppBundle\Command;

use AppBundle\Elasticsearch\Type\IndexablePersonalProject;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportPersonalProjectCommand extends BaseCommand
{
    /**
     * Configure command
     *
     */
    protected function configure()
    {
        $this
            ->setName('wamcar:populate:personal_project')
            ->setDescription('Populate the personal project search with data from the persoanl projects entity');
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
        $personalProjectRepository = $this->getContainer()->get('AppBundle\Doctrine\Repository\ProjectRepository');
        $indexablePersonalProjectBuilder = $this->getContainer()->get('AppBundle\Elasticsearch\Builder\IndexablePersonalProjectBuilder');

        $projects = $personalProjectRepository->findAll();
        $progress = new ProgressBar($output, count($projects));

        foreach ($projects as $project) {
            $progress->advance();
            $objectIndexer->index($indexablePersonalProjectBuilder->buildFromProject($project), IndexablePersonalProject::TYPE);
        }

        $progress->finish();

        $this->logCRLF();
        $this->log('success', 'Done ! It\'s possible that projects are not indexed if empty.');
    }

}
