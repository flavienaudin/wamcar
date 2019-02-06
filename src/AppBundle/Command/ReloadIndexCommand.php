<?php

namespace AppBundle\Command;

use AppBundle\Elasticsearch\Elastica\EntityIndexBuilder;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ReloadIndexCommand extends ContainerAwareCommand
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
            ->addOption('indices', 'i', InputOption::VALUE_OPTIONAL,
                'Names of index to reload, separated by a coma (,)',
                "vehicle_info,city,pro_vehicle,personal_vehicle,personal_project,pro_user,search_item"
            )
            ->addOption('populate', 'p', InputOption::VALUE_OPTIONAL,
                'If true then indices will be populated',
                false)
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

        $io = new SymfonyStyle($input, $output);
        $indices = explode(',', $input->getOption('indices'));
        $populate = $input->getOption('populate');
        foreach ($indices as $index) {
            /** @var EntityIndexBuilder $entityIndexBuilder */
            $entityIndexBuilder = $this->getContainer()->get($index . '.index_builder');
            $io->text('Reloading index : ' . $entityIndexBuilder->getIndexName());
            $entityIndexBuilder->create();
            if($populate){
                $indexToPopulateCommand = [
                    "vehicle_info" => ["name" => 'wamcar:populate:vehicle_info', 'arguments' => []],
                    "city" => ["name" => 'wamcar:populate:es-cities', 'arguments' => []],
                    "pro_vehicle" => ["name" => 'wamcar:populate:pro_vehicle', 'arguments' => []],
                    "personal_vehicle" => ["name" => 'wamcar:populate:personal_vehicle', 'arguments' => []],
                    "personal_project" => ["name" => 'wamcar:populate:personal_project', 'arguments' => []],
                    "pro_user" => ["name" => 'wamcar:directory:index_pro_users', 'arguments' => []],
                    "search_item" => ["name" => 'wamcar:populate:search_item', 'arguments' => []],
                ];
                try{
                    $cmd = $indexToPopulateCommand[$index];
                    $command = $this->getApplication()->find($cmd['name']);
                    $cmdInput = new ArrayInput($cmd['arguments']);
                    $command->run($cmdInput, $output);
                } catch (\Exception $e) {
                    $io->error($e->getMessage());
                    $io->error($e->getTraceAsString());
                }
            }
        }
        $io->success('Done !');
    }

}
