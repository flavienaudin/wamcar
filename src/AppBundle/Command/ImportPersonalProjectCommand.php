<?php

namespace AppBundle\Command;

use AppBundle\Elasticsearch\Type\IndexablePersonalProject;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ImportPersonalProjectCommand extends ContainerAwareCommand
{
    /**
     * Configure command
     *
     */
    protected function configure()
    {
        $this
            ->setName('wamcar:populate:personal_project')
            ->setDescription('Populate the personal project search with data from the personal projects entity');
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

        $personalProjectIndexer = $this->getContainer()->get('personal_project.indexer');
        $personalProjectRepository = $this->getContainer()->get('AppBundle\Doctrine\Repository\ProjectRepository');

        $projects = $personalProjectRepository->findAll();
        $personalProjectDocuments = [];
        $io->text('Personal Project reading');
        $io->progressStart(count($projects));
        foreach ($projects as $project) {
            $indexablePersonalProject = IndexablePersonalProject::createFromPersonalProject($project);
            if ($indexablePersonalProject->shouldBeIndexed()) {
                $personalProjectDocuments[] = $personalProjectIndexer->buildDocument($indexablePersonalProject);
            }
            $io->progressAdvance();
        }
        $io->progressFinish();
        $io->newLine();

        $io->text('Indexing ' . count($personalProjectDocuments) . ' personal projects');
        $personalProjectIndexer->indexAllDocuments($personalProjectDocuments, true);

        $io->success('Done ! It\'s possible that projects are not indexed if empty.');
    }

}
