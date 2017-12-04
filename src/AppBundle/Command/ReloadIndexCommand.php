<?php

namespace AppBundle\Command;

use AppBundle\Elasticsearch\Type\IndexablePersonalVehicle;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ReloadIndexCommand extends BaseCommand
{
    /**
     * Configure command
     *
     */
    protected function configure()
    {
        $this
            ->setName('wamcar:populate:clear_index')
            ->setDescription('Clear the index')
            ;
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

        $index->reload();

        $this->log('info', 'Reload !');

        $this->logCRLF();
        $this->log('success', 'Done !');
    }

}
