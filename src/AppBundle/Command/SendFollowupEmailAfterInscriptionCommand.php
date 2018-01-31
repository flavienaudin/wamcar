<?php

namespace AppBundle\Command;

use AppBundle\Services\User\UserGlobalSearchService;
use SimpleBus\Message\Bus\MessageBus;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Wamcar\User\Event\AddingPicturesToVehicleNotification;

class SendFollowupEmailAfterInscriptionCommand extends BaseCommand
{
    /** @var UserGlobalSearchService */
    private $userGlobalSearchService;

    /** @var MessageBus */
    private $eventBus;

    public function __construct(UserGlobalSearchService $userGlobalSearchService, MessageBus $eventBus)
    {
        parent::__construct();
        $this->userGlobalSearchService = $userGlobalSearchService;
        $this->eventBus = $eventBus;
    }

    /**
     * Configure command
     *
     */
    protected function configure()
    {
        $this
            ->setName('wamcar:email:after_personal_inscription')
            ->setDescription('Send a reminder e-mail to personal who set zero or one picture to their vehicle, 24h after their inscription.');
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

        $personals = $this->userGlobalSearchService->findPersonalToRemind();

        $progress = new ProgressBar($output, count($personals));
        foreach ($personals as $personal) {
            $progress->advance();
            // a mail will be send to the user on the event handling
            $this->eventBus->handle(new AddingPicturesToVehicleNotification($personal));
        }

        $progress->finish();

        $this->logCRLF();
        $this->log('success', 'Done !');
    }

}