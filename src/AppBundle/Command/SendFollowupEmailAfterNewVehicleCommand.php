<?php

namespace AppBundle\Command;

use AppBundle\Services\Vehicle\PersonalVehicleEditionService;
use SimpleBus\Message\Bus\MessageBus;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Wamcar\Vehicle\Event\AddingPicturesToVehicleNotification;

class SendFollowupEmailAfterNewVehicleCommand extends BaseCommand
{
    /** @var PersonalVehicleEditionService */
    private $personalVehicleEditionService;

    /** @var MessageBus */
    private $eventBus;

    public function __construct(PersonalVehicleEditionService $personalVehicleEditionService, MessageBus $eventBus)
    {
        parent::__construct();
        $this->personalVehicleEditionService = $personalVehicleEditionService;
        $this->eventBus = $eventBus;
    }

    /**
     * Configure command
     *
     */
    protected function configure()
    {
        $this
            ->setName('wamcar:email:after_new_vehicle')
            ->setDescription('Send a reminder e-mail to personal who set zero or one picture to their vehicle, 24h after the vehicle registration.');
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
        $this->log('notice', 'Starting at '.date(\DateTime::ISO8601));

        $personals = $this->personalVehicleEditionService->findPersonalToRemind();

        $progress = new ProgressBar($output, count($personals));
        foreach ($personals as $personal) {
            $progress->advance();
            // a mail will be send to the user on the event handling
            $this->eventBus->handle(new AddingPicturesToVehicleNotification($personal));
        }

        $progress->finish();

        $this->logCRLF();
        $this->log('success', 'Done at : ' . date(\DateTime::ISO8601));
        $this->logCRLF();
    }

}