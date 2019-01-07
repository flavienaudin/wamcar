<?php

namespace AppBundle\Command;


use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportAllDataCommand extends BaseCommand
{
    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this
            ->setName('wamcar:populate:all-es')
            ->setDescription('Populate ES with : Cities, VehicleInfo, PersonalVehicle, PersonalProject, ProVehicle, ProUser (directory)');

    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;

        $commands = [
            'wamcar:populate:personal_vehicle' => [],
            'wamcar:populate:personal_project' => [],
            'wamcar:populate:pro_vehicle' => [],
            'wamcar:directory:index_pro_users' => [],
            'wamcar:populate:vehicle_info' => [
                'file' => __DIR__ . '/../../../database/fixtures/base_vehicule_20180228.csv'
            ],
            'wamcar:populate:es-cities' => []
        ];
        try {
            foreach ($commands as $commandName => $inputArgument) {
                $this->log('info', $commandName);
                $command = $this->getApplication()->find($commandName);
                $greetInput = new ArrayInput($inputArgument);
                $returnCode = $command->run($greetInput, $output);
                $this->log('info', sprintf('%s : %d', $commandName, $returnCode));
                $this->log('info', '------------------');
            }
        } catch (\Exception $e) {
            $this->log('error', $e->getMessage());
            $this->log('error', $e->getTraceAsString());
        }
    }


}