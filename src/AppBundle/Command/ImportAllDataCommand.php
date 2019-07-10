<?php

namespace AppBundle\Command;


use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ImportAllDataCommand extends BaseCommand
{
    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this
            ->setName('wamcar:populate:all-es')
            ->setDescription('Populate ES with : Cities, VehicleInfo, PersonalVehicle, PersonalProject, ProVehicle, ProUser (directory), SearchItem');

    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $commands = [
            'wamcar:populate:personal_vehicle' => [],
            'wamcar:populate:personal_project' => [],
            'wamcar:populate:pro_vehicle' => [],
            'wamcar:directory:index_pro_users' => [],
            'wamcar:populate:vehicle_info' => [
                'file' => __DIR__ . '/../../../database/fixtures/base_vehicule_20181122.csv'
            ],
            'wamcar:populate:es-cities' => []
        ];
        try {
            foreach ($commands as $commandName => $inputArgument) {
                $io->text($commandName);
                $command = $this->getApplication()->find($commandName);
                $greetInput = new ArrayInput($inputArgument);
                $returnCode = $command->run($greetInput, $output);

                $io->text(sprintf('%s : %d', $commandName, $returnCode));
                $io->text('------------------');
            }
            $io->success("Done at " . date(self::DATE_FORMAT));
        } catch (\Exception $e) {
            $io->error($e->getMessage());
            $io->error($e->getTraceAsString());
        }
    }
}