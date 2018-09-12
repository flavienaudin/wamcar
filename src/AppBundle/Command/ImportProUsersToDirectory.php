<?php

namespace AppBundle\Command;


use AppBundle\Elasticsearch\Type\IndexableProUser;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportProUsersToDirectory extends BaseCommand
{
    /**
     * Configure command
     *
     */
    protected function configure()
    {
        $this
            ->setName('wamcar:directory:index_pro_users')
            ->setDescription('Index professionals to the directory');
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
        $proUserRepository = $this->getContainer()->get('AppBundle\Doctrine\Repository\DoctrineProUserRepository');

        $proUsers= $proUserRepository->findAll();
        $progress = new ProgressBar($output, count($proUsers));

        foreach ($proUsers as $proUser) {
            $progress->advance();
            $objectIndexer->index(IndexableProUser::createFromProApplicationUser($proUser), IndexableProUser::TYPE);
        }

        $progress->finish();

        $this->logCRLF();
        $this->log('success', 'Done ! ');
    }

}