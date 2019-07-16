<?php

namespace AppBundle\Command;


use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Wamcar\Garage\Garage;
use Wamcar\User\BaseUser;

class GenerateMissingApiCredentialsCommand extends BaseCommand
{

    const SOFTDELETED_ARGUMENT = "softdeleted";

    /**
     * Configure command
     *
     */
    protected function configure()
    {
        $this
            ->setName('wamcar:generate:missingApiCredentials')
            ->setDescription('Generate missing API credentials')
            ->addOption(self::SOFTDELETED_ARGUMENT, null,
                InputOption::VALUE_NONE,
                'Generate missing api credentials for soft deleted entities');

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
        $userRepository = $this->getContainer()->get('AppBundle\Doctrine\Repository\DoctrineUserRepository');

        if ($input->getOption(self::SOFTDELETED_ARGUMENT) === true) {
            $io->text('Option : true');
            $users = $userRepository->findIgnoreSoftDeletedBy(["apiClientId" => null]);
        } else {
            $io->text('Option : false');
            $users = $userRepository->findBy(["apiClientId" => null]);
        }
        $io->text("Users");
        $io->progressStart(count($users));
        /** @var BaseUser $user */
        foreach ($users as $user) {
            $io->progressAdvance();
            $user->generateApiCredentials();
            $userRepository->update($user);
        }
        $io->progressFinish();

        $garageRepository = $this->getContainer()->get('AppBundle\Doctrine\Repository\DoctrineGarageRepository');
        if ($input->getOption(self::SOFTDELETED_ARGUMENT) === true) {
            $garages = $garageRepository->findIgnoreSoftDeletedBy(["apiClientId" => null]);
        } else {
            $garages = $garageRepository->findBy(["apiClientId" => null]);
        }
        $io->text("Garages");
        $io->progressStart(count($garages));
        /** @var Garage $garage */
        foreach ($garages as $garage) {
            $io->progressAdvance();
            $garage->generateApiCredentials();
            $garageRepository->update($garage);
        }
        $io->progressFinish();
        $io->success("Done at " . date(self::DATE_FORMAT));
    }
}