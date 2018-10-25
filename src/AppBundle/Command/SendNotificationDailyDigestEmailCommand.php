<?php

namespace AppBundle\Command;


use AppBundle\MailWorkflow\Model\EmailContact;
use AppBundle\MailWorkflow\Model\EmailRecipientList;
use AppBundle\MailWorkflow\Services\Mailer;
use AppBundle\Services\Notification\NotificationManagerExtended;
use Mgilet\NotificationBundle\Entity\NotifiableEntity;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Translation\TranslatorInterface;

class SendNotificationDailyDigestEmailCommand extends BaseCommand
{

    /** @var NotificationManagerExtended $notificationManagerExtended */
    private $notificationManagerExtended;
    /** @var Mailer $mailer */
    private $mailer;
    /** @var UrlGeneratorInterface $router */
    protected $router;
    /** @var EngineInterface $templating */
    protected $templating;
    /** @var TranslatorInterface */
    protected $translator;

    /**
     * SendNotificationDailyDigestEmailCommand constructor.
     * @param NotificationManagerExtended $notificationManagerExtended
     * @param Mailer $mailer
     * @param UrlGeneratorInterface $router
     * @param EngineInterface $templating
     * @param TranslatorInterface $translator
     */
    public function __construct(NotificationManagerExtended $notificationManagerExtended,
                                Mailer $mailer,
                                UrlGeneratorInterface $router,
                                EngineInterface $templating,
                                TranslatorInterface $translator)
    {
        parent::__construct();
        $this->notificationManagerExtended = $notificationManagerExtended;
        $this->mailer = $mailer;
        $this->router = $router;
        $this->templating = $templating;
        $this->translator = $translator;
    }


    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this
            ->setName('wamcar:email:notification_daily_digest')
            ->setDescription('Send daily digest of notification to user');
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        $this->log('notice', 'Starting at ' . date(\DateTime::ISO8601));

        $progress = null;
        try {
            $notifiables = $this->notificationManagerExtended->getNotifiablesWithEmailableNotification();

            $progress = new ProgressBar($output, count($notifiables));
            foreach ($notifiables as $notifiable) {
                $progress->advance();

                /** @var NotifiableEntity $notifiableEntity */
                $notifiableEntity = $notifiable['notifiableEntity'];

                $this->mailer->sendMessage(
                    'unseen_notification',
                    $this->translator->trans('unseenNotificationsDailyDigest.object', [], 'email'),
                    $this->templating->render('Mail/unseenNotificationsDailyDigest.html.twig', [
                        'username' => $notifiable['recipient_firstname'],
                        'nbUnseenNotifications' => count($notifiableEntity->getNotifiableNotifications()),
                        'message_url' => $this->router->generate('notification_list', ['notifiable' => $notifiableEntity->getId()], UrlGeneratorInterface::ABSOLUTE_URL)

                    ]),
                    new EmailRecipientList([new EmailContact($notifiable['recipient_email'], $notifiable['recipient_firstname'] . ' ' . $notifiable['recipient_lastname'])])
                );
            }
            $progress->finish();

            $this->logCRLF();
            $this->log('success', 'Done at : ' . date(\DateTime::ISO8601));
        } catch (\Exception $exception) {
            $this->log(parent::ERROR, $exception->getMessage());
            $this->log(parent::ERROR, $exception->getTraceAsString());
            if ($progress instanceof ProgressBar) {
                $progress->finish();
            }
        }
    }
}