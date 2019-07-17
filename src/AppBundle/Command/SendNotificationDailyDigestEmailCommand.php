<?php

namespace AppBundle\Command;


use AppBundle\Doctrine\Entity\PersonalApplicationUser;
use AppBundle\Doctrine\Entity\ProApplicationUser;
use AppBundle\MailWorkflow\Model\EmailContact;
use AppBundle\MailWorkflow\Model\EmailRecipientList;
use AppBundle\MailWorkflow\Services\Mailer;
use AppBundle\Services\Notification\NotificationManagerExtended;
use AppBundle\Services\User\UserEditionService;
use Mgilet\NotificationBundle\Entity\NotifiableEntity;
use Mgilet\NotificationBundle\Entity\NotifiableNotification;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Wamcar\Conversation\MessageRepository;
use Wamcar\User\BaseUser;

class SendNotificationDailyDigestEmailCommand extends BaseCommand
{

    /** @var UserEditionService userEditionService */
    private $userEditionService;
    /** @var NotificationManagerExtended $notificationManagerExtended */
    private $notificationManagerExtended;
    /** @var MessageRepository */
    private $messageRepository;
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
     * @param MessageRepository $messageRepository
     * @param UserEditionService $userEditionService
     * @param Mailer $mailer
     * @param UrlGeneratorInterface $router
     * @param EngineInterface $templating
     * @param TranslatorInterface $translator
     */
    public function __construct(NotificationManagerExtended $notificationManagerExtended,
                                MessageRepository $messageRepository,
                                UserEditionService $userEditionService,
                                Mailer $mailer,
                                UrlGeneratorInterface $router,
                                EngineInterface $templating,
                                TranslatorInterface $translator)
    {
        parent::__construct();
        $this->notificationManagerExtended = $notificationManagerExtended;
        $this->messageRepository = $messageRepository;
        $this->userEditionService = $userEditionService;
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
            ->setDescription('Send daily digest of notification to user')
            ->addOption('hours', null, InputOption::VALUE_OPTIONAL,
                'Number of hours to treat : for how number of hours we check for unread notification or message',
                24);
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $io->text('Starting at ' . date(\DateTime::ISO8601));

        $progress = null;
        try {
            $hours = $input->getOption('hours');
            $usersToEmail = $this->userEditionService->getUserWithEmailableUnreadNotifications($hours);

            $io->progressStart(count($usersToEmail));
            /** @var BaseUser $userToEmail */
            foreach ($usersToEmail as $userToEmail) {
                $io->progressAdvance();


                $nbUnreadNotifications = 0;
                if ($userToEmail instanceof ProApplicationUser or $userToEmail instanceof PersonalApplicationUser) {
                    $notifiableEntity = $this->notificationManagerExtended->getNotificationManager()->getNotifiableEntity($userToEmail);
                }
                $countUnreadMessage = $this->messageRepository->getCountUnreadMessagesByUser($userToEmail);

                $trackingKeywords = ($userToEmail->isPro() ? 'advisor' : 'customer') . $userToEmail->getId();
                $commonUTM = [
                    'utm_source' => 'notifications',
                    'utm_medium' => 'email',
                    'utm_campaign' => 'new_notifications',
                    'utm_term' => $trackingKeywords
                ];
                $this->mailer->sendMessage(
                    'unseen_notification',
                    $this->translator->trans('unseenNotificationsDailyDigest.object', [], 'email'),
                    $this->templating->render('Mail/unseenNotificationsDailyDigest.html.twig', [
                        'common_utm' => $commonUTM,
                        'username' => $userToEmail->getFirstName(),
                        'nbUnseenNotifications' => ($notifiableEntity ?
                            count($notifiableEntity->getNotifiableNotifications()->filter(function (NotifiableNotification $nn) {
                                return !$nn->isSeen();
                            })) : 0),
                        'nbUnseenMessages' => $countUnreadMessage,
                        'conversationsListURL' => $this->router->generate('front_conversation_list', array_merge($commonUTM, [
                           'utm_cotent' => 'bouton_messagerie'
                          ]), UrlGeneratorInterface::ABSOLUTE_URL),
                        'notificationListUrl' => $this->router->generate('notification_list', array_merge($commonUTM, [
                            'notifiable' => $notifiableEntity->getId(),
                            'utm_content' => 'bouton_notification'
                        ]), UrlGeneratorInterface::ABSOLUTE_URL)

                    ]),
                    new EmailRecipientList([new EmailContact($userToEmail->getEmail(), $userToEmail->getFullName())])
                );
            }
            $io->progressFinish();

            $io->success('Done at : ' . date(\DateTime::ISO8601));
        } catch (\Exception $exception) {
            $io->error($exception->getMessage());
            $io->error($exception->getTraceAsString());
            if ($progress instanceof ProgressBar) {
                $progress->finish();
            }
        }
    }
}