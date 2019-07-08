<?php


namespace AppBundle\Command;


use AppBundle\Services\User\LeadManagementService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Prévenir les pros, qu'un utilisateur s'est inscrit dans leurs critères de sélection, par notification et e-mail.
 * L'interval pour sélectionner les nouvelles inscriptions est défini par :
 *  - la date d'exécution du script moins 1 heure : pour éviter de prendre les utilisateurs en cours d'inscription et de
 * saisie de leur projet et/ou reprise
 *  - la date d'exécution du script moins 1 heure moins le temps entre deux exécutions de script, passé en paramètre
 *
 * Class InformProUsersOfNewPotentialLeadsCommand
 * @package AppBundle\Command
 */
class InformProUsersOfNewPotentialLeadsCommand extends BaseCommand
{

    /** @var LeadManagementService */
    private $leadManagementService;

    /**
     * InformProUsersOfNewPotentialLeadsCommand constructor.
     * @param LeadManagementService $leadManagementService
     */
    public function __construct(LeadManagementService $leadManagementService)
    {
        parent::__construct();
        $this->leadManagementService = $leadManagementService;
    }

    /**
     * Configure command
     */
    protected function configure()
    {
        $this
            ->setName('wamcar:notify:new-personal-user')
            ->setDescription('Inform pro user of new potetial lead in their search')
            ->addOption('delay', null, InputOption::VALUE_REQUIRED,
                'The script execution reccurence time in hours: Personal users registration between 
                the current datetime - 1 hour and the current datetime - 1 hour - delay are treated.');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $delay = $input->getOption('delay');
        $io->text($delay);

        $this->leadManagementService->informProUserOfNewUser($delay);

        $io->success('Done at : ' . date(\DateTime::ISO8601));
    }
}