<?php


namespace AppBundle\Command;


use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CleanVehiclesPicturesCommand extends BaseCommand
{

    protected function configure()
    {
        $this
            ->setName('wamcar:clean:vehicle_pictures')
            ->setDescription('Clean the pictures of softdeleted vehicles and keep only the main picture.')
            ->addOption('month',
                'm',
                InputOption::VALUE_OPTIONAL,
                'Ignore the vehicles softdeleted during the last "m" months. Default : 3 months',
                3
            );
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
        $io->text('Started at ' . date(self::DATE_FORMAT));

        $vehicleEditionService = $this->getContainer()->get('AppBundle\Services\Vehicle\ProVehicleEditionService');

        $months = $input->getOption('month');
        $results = $vehicleEditionService->clearSoftDeletedVehiclesPictures($months, $io);
        $io->text('Total : ' . $results['total'] . ' pictures removed');
        $io->success('Done at ' . date(self::DATE_FORMAT));
    }
}