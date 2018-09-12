<?php

namespace AppBundle\Command;


use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


abstract class AbstractClearCommand extends BaseCommand
{
    const BATCH_SIZE = 10000;

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
        parent::__construct($name);
        $this->description = $description;
        $this->indexableType = $indexableType;
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
        $this->output = $output;

        $index = $this->getContainer()->get('Novaway\ElasticsearchClient\Index');
        $objectIndexer = $this->getContainer()->get('Novaway\ElasticsearchClient\ObjectIndexer');

        $progress = null;
        do {
            $result = $index->search([
                'type' => $this->indexableType,
                'size' => self::BATCH_SIZE
            ]);
            if ($progress === null) {
                $progress = new ProgressBar($output, $result->totalHits());
            }

            $nbCurrentHits = count($result->hits());
            foreach ($result->hits() as $hit) {
                $progress->advance();
                $objectIndexer->removeById($hit['id'], $this->indexableType);
            }
            // To let the deletion propagation occurs
            sleep(1);
        } while ($nbCurrentHits >= self::BATCH_SIZE);

        $progress->finish();
        $this->logCRLF();
        $this->log('success', 'Done !');
    }

}