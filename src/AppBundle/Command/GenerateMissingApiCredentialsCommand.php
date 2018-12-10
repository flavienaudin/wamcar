<?php

namespace AppBundle\Command;


use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Wamcar\Garage\Garage;
use Wamcar\User\BaseUser;

class GenerateMissingApiCredentialsCommand extends BaseCommand
{


    /**
     * Configure command
     *
     */
    protected function configure()
    {
        $this
            ->setName('wamcar:generate:missingApiCredentials')
            ->setDescription('Generate missing API credentials');
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

        $userRepository = $this->getContainer()->get('AppBundle\Doctrine\Repository\DoctrineUserRepository');
        $users = $userRepository->findBy(["apiClientId" => null]);
        $this->log(self::INFO, "Users");
        $progressUsers = new ProgressBar($this->output, count($users));
        /** @var BaseUser $user */
        foreach ($users as $user) {
            $progressUsers->advance();
            $user->generateApiCredentials();
            $userRepository->update($user);
        }
        $progressUsers->finish();
        $this->logCRLF();

        $garageRepository = $this->getContainer()->get('AppBundle\Doctrine\Repository\DoctrineGarageRepository');
        $garages = $garageRepository->findBy(["apiClientId" => null]);
        $this->log(self::INFO, "Garages");
        $progressGarages = new ProgressBar($this->output, count($garages));
        /** @var Garage $garage */
        foreach ($garages as $garage) {
            $progressGarages->advance();
            $garage->generateApiCredentials();
            $garageRepository->update($garage);
        }
        $progressGarages->finish();

        $this->logCRLF();
        $this->log('success', 'Done !');
    }
}