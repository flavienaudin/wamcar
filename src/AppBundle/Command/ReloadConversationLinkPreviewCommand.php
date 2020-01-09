<?php


namespace AppBundle\Command;


use AppBundle\Services\Conversation\ConversationEditionService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ReloadConversationLinkPreviewCommand extends BaseCommand
{

    /** @var ConversationEditionService */
    private $conversationEditionService;

    public function __construct(ConversationEditionService $conversationEditionService)
    {
        parent::__construct();
        $this->conversationEditionService = $conversationEditionService;
    }

    /**
     * Configure command
     *
     */
    protected function configure()
    {
        $this
            ->setName('wamcar:conversation:reload_link_preview')
            ->setDescription('Clear and reload the link preview in all conversation messages');
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
        $this->conversationEditionService->clearAndReloadMessageLinkPreviews($io);
        $io->text('End at : ' . date(\DateTime::ISO8601));
    }
}