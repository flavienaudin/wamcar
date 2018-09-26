<?php

namespace AppBundle\Command;


use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
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
        $progress = new ProgressBar($this->output, count($users));
        /** @var BaseUser $user */
        foreach ($users as $user) {
            $progress->advance();

            $user->generateApiCredentials();
            $userRepository->update($user);
        }

        $progress->finish();

        $this->logCRLF();
        $this->log('success', 'Done !');
    }
}