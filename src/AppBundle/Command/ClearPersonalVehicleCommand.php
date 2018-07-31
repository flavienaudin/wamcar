<?php

namespace AppBundle\Command;

use AppBundle\Elasticsearch\Type\IndexablePersonalVehicle;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ClearPersonalVehicleCommand extends BaseCommand
{
    const BATCH_SIZE = 10000;

    /**
     * Configure command
     *
     */
    protected function configure()
    {
        $this
            ->setName('wamcar:clear:personal_vehicle')
            ->setDescription('Clear all personal vehicles from the index');
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
            $vehicles = $index->search([
                'type' => IndexablePersonalVehicle::TYPE,
                'size' => self::BATCH_SIZE
            ]);
            if ($progress === null) {
                $progress = new ProgressBar($output, $vehicles->totalHits());
            }

            $nbCurrentHits = count($vehicles->hits());
            foreach ($vehicles->hits() as $vehicle) {
                $progress->advance();
                $objectIndexer->removeById($vehicle['id'], IndexablePersonalVehicle::TYPE);
            }
            sleep(1);
        } while ($nbCurrentHits >= self::BATCH_SIZE);

        $progress->finish();
        $this->logCRLF();
        $this->log('success', 'Done !');
    }

}
