<?php

namespace AppBundle\Command;


use AppBundle\Elasticsearch\Type\IndexableProUser;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ImportProUsersToDirectoryCommand extends BaseCommand
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
        $io = new SymfonyStyle($input, $output);

        $proUserIndexer = $this->getContainer()->get('pro_user.indexer');
        $proUserRepository = $this->getContainer()->get('AppBundle\Doctrine\Repository\DoctrineProUserRepository');

        $proUsers = $proUserRepository->findAll();
        $proUserDocuments = [];
        $io->text('Pro User reading');
        $io->progressStart(count($proUsers));
        foreach ($proUsers as $proUser) {
            $indexableProUser = IndexableProUser::createFromProApplicationUser($proUser);
            if ($indexableProUser->shouldBeIndexed()) {
                $proUserDocuments[] = $proUserIndexer->buildDocument($indexableProUser);
            }
            $io->progressAdvance();
        }
        $io->progressFinish();

        $io->text('Indexing ' . count($proUserDocuments) . ' pro users');
        $proUserIndexer->indexAllDocuments($proUserDocuments, true);

        $io->success("Done at " . date(self::DATE_FORMAT));
    }

}