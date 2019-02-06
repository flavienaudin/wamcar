<?php

namespace AppBundle\Command;


use AppBundle\Elasticsearch\Elastica\EntityIndexer;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;


abstract class AbstractClearCommand extends ContainerAwareCommand
{
    /** @var string */
    private $description;
    /** @var string */
    private $indexableType;

    /**
     * AbstractClearCommand constructor.
     * @param string $name
     * @param string $description
     * @param string $indexableType
     */
    public function __construct(string $name, string $description, string $indexableType)
    {
        $this->description = $description;
        $this->indexableType = $indexableType;
        parent::__construct($name);
    }

    /**
     * Configure command
     *
     */
    protected function configure()
    {
        $this->setDescription($this->description);
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
        /** @var EntityIndexer $entityIndexer */
        $entityIndexer = $this->getContainer()->get($this->indexableType . '.indexer');
        $entityIndexer->deleteAllDocuments();
        $io->success('Done !');
    }

}