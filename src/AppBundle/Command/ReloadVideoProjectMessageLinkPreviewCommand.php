<?php


namespace AppBundle\Command;


use AppBundle\Services\VideoCoaching\VideoProjectService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ReloadVideoProjectMessageLinkPreviewCommand extends BaseCommand
{

    /** @var VideoProjectService */
    private $videoProjectService;

    /**
     * ReloadVideoProjectMessageLinkPreviewCommand constructor.
     * @param VideoProjectService $videoProjectService
     */
    public function __construct(VideoProjectService $videoProjectService)
    {
        parent::__construct();
        $this->videoProjectService = $videoProjectService;
    }

    /**
     * Configure command
     *
     */
    protected function configure()
    {
        $this
            ->setName('wamcar:videoproject:reload_message_link_preview')
            ->setDescription('Clear and reload the link preview in all video project messages');
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

        $io->text('Start at : ' . date(\DateTime::ISO8601));
        $this->videoProjectService->clearAndReloadVideoProjectMessageLinkPreviews($io);
        $io->text('End at : ' . date(\DateTime::ISO8601));
    }
}