<?php

namespace AppBundle\Command;

use AppBundle\Elasticsearch\Type\IndexablePersonalProject;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ClearPersonalProjectCommand extends BaseCommand
{
    const BATCH_SIZE = 10000;

    /**
     * Configure command
     *
     */
    protected function configure()
    {
        $this
            ->setName('wamcar:clear:personal_project')
            ->setDescription('Clear all personal projects from the index');
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
                'type' => IndexablePersonalProject::TYPE,
                'size' => self::BATCH_SIZE
            ]);
            if ($progress === null) {
                $progress = new ProgressBar($output, $result->totalHits());
            }

            $nbCurrentHits = count($result->hits());
            foreach ($result->hits() as $hit) {
                $progress->advance();
                $objectIndexer->removeById($hit['id'], IndexablePersonalProject::TYPE);
            }
            // To let the deletion propagation occurs
            sleep(1);
        } while ($nbCurrentHits >= self::BATCH_SIZE);

        $progress->finish();
        $this->logCRLF();
        $this->log('success', 'Done !');
    }

}
