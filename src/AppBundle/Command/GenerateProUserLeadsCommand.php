<?php

namespace AppBundle\Command;


use AppBundle\Doctrine\Repository\DoctrineProUserRepository;
use AppBundle\Services\User\LeadManagementService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Wamcar\User\ProUser;

class GenerateProUserLeadsCommand extends BaseCommand
{

    /** @var DoctrineProUserRepository $proUserRepository */
    private $proUserRepository;
    /** @var LeadManagementService $leadManagementService */
    private $leadManagementService;

    /**
     * GenerateProUserLeadsCommand constructor.
     * @param DoctrineProUserRepository $proUserRepository
     * @param LeadManagementService $leadManagementService
     */
    public function __construct(DoctrineProUserRepository $proUserRepository, LeadManagementService $leadManagementService)
    {
        parent::__construct('wamcar:generate:pro_user_leads');
        $this->setDescription('Generate missing "leads" of pro users');
        $this->proUserRepository = $proUserRepository;
        $this->leadManagementService = $leadManagementService;
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
        $proUsers = $this->proUserRepository->findAll();
        $io->progressStart(count($proUsers));
        $nbLeadsByProUser = [];
        /** @var ProUser $proUser */
        foreach ($proUsers as $proUser) {
            $nbLeadsByProUser[] = [$proUser->getId(), $this->leadManagementService->generateProUserLead($proUser)];
            $io->progressAdvance();
        }
        $io->progressFinish();

        $io->table(['ProUserId', 'Nb of leads'], $nbLeadsByProUser);
        $io->success('Done !');
    }
}