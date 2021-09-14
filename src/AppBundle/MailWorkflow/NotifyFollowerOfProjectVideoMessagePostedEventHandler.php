<?php


namespace AppBundle\MailWorkflow;


use AppBundle\MailWorkflow\Model\EmailRecipientList;
use AppBundle\MailWorkflow\Services\Mailer;
use AppBundle\Services\Notification\NotificationManagerExtended;
use Doctrine\ORM\OptimisticLockException;
use Mgilet\NotificationBundle\Manager\NotificationManager;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Wamcar\Vehicle\Enum\NotificationFrequency;
use Wamcar\VideoCoaching\Event\VideoProjectMessageEvent;
use Wamcar\VideoCoaching\Event\VideoProjectMessageEventHandler;
use Wamcar\VideoCoaching\Event\VideoProjectMessagePostedEvent;
use Wamcar\VideoCoaching\VideoProjectMessage;
use Wamcar\VideoCoaching\VideoProjectViewer;

class NotifyFollowerOfProjectVideoMessagePostedEventHandler extends AbstractEmailEventHandler implements VideoProjectMessageEventHandler
{

    /** @var NotificationManager $notificationsManager */
    protected $notificationsManager;
    /** @var NotificationManagerExtended $notificationsManagerExtended */
    protected $notificationsManagerExtended;

    /**
     * NotifyFollowerOfProjectVideoMessagePostedEventHandler constructor.
     * @param Mailer $mailer
     * @param UrlGeneratorInterface $router
     * @param EngineInterface $templating
     * @param TranslatorInterface $translator
     * @param string $type
     * @param NotificationManager $notificationsManager
     * @param NotificationManagerExtended $notificationsManagerExtended
     */
    public function __construct(
        Mailer $mailer,
        UrlGeneratorInterface $router,
        EngineInterface $templating,
        TranslatorInterface $translator,
        string $type,
        NotificationManager $notificationsManager,
        NotificationManagerExtended $notificationsManagerExtended
    )
    {
        parent::__construct($mailer, $router, $templating, $translator, $type);
        $this->notificationsManager = $notificationsManager;
        $this->notificationsManagerExtended = $notificationsManagerExtended;
    }

    /**
     * @param VideoProjectMessageEvent $event
     */
    public function notify(VideoProjectMessageEvent $event)
    {
        $this->checkEventClass($event, VideoProjectMessagePostedEvent::class);

        /** @var VideoProjectMessage $videoProjectMessage */
        $videoProjectMessage = $event->getVideoProjectMessage();
        $author = $videoProjectMessage->getAuthor();
        $notificationData = json_encode(['id' => $videoProjectMessage->getId()]);
        $followers = $videoProjectMessage->getVideoProject()->getViewers(true);

        /** @var VideoProjectViewer $follower */
        foreach ($followers as $follower) {
            if (!$author->is($follower)) {
                // Create notification
                $notifications = $this->notificationsManagerExtended->createNotification(
                    get_class($videoProjectMessage),
                    get_class($event),
                    $notificationData,
                    $this->router->generate('front_coachingvideo_videoproject_view', ['videoProjectId' => $videoProjectMessage->getVideoProject()->getId()])
                );
                try {
                    $this->notificationsManager->addNotification([$follower->getViewer()], $notifications, true);
                } catch (OptimisticLockException $e) {
                    // tant pis pour la notification, on ne bloque pas l'action
                }

                // Send email according to user preference
                if ($follower->getViewer()->getPreferences()->isVideoProjectNewMessageEmailEnabled() &&
                    NotificationFrequency::IMMEDIATELY()->equals($follower->getViewer()->getPreferences()->getVideoProjectNewMessageEmailFrequency())
                ) {
                    $emailObject = $this->translator->trans('notifyFollowersOfVideoProjectMessagePosted.object', [
                        '%authorFullName%' => $author->getFullName(),
                        '%videoProjectTitle%' => $videoProjectMessage->getVideoProject()->getTitle()
                    ], 'email');

                    $trackingKeywords = 'videoProject' . $videoProjectMessage->getVideoProject()->getId() . '-follower' . $follower->getViewer()->getId();

                    $commonUTM = [
                        'utm_source' => 'notifications',
                        'utm_medium' => 'email',
                        'utm_campaign' => 'video_project_message_follower',
                        'utm_term' => $trackingKeywords
                    ];

                    $this->send(
                        $emailObject,
                        'Mail/notifyFollowersOfVideoProjectMessagePosted.html.twig',
                        [
                            'emailObject' => $emailObject,
                            'common_utm' => $commonUTM,
//                'transparentPixel' => [
//                    'tid' => 'UA-73946027-1',
//                    'cid' => $author->getUserID(),
//                    't' => 'event',
//                    'ec' => 'email',
//                    'ea' => 'open',
//                    'el' => urlencode($emailObject),
//                    'dh' => $this->router->getContext()->getHost(),
//                    'dp' => urlencode('/email/procontact/open/' . $videoProjectMessage->getId()),
//                    'dt' => urlencode($emailObject),
//                    'cs' => 'notifications', // Campaign source
//                    'cm' => 'email', // Campaign medium
//                    'cn' => 'procontact', // Campaign name
//                    'ck' => $trackingKeywords, // Campaign Keyword (/ terms)
//                    'cc' => 'opened', // Campaign content
//                ],
                            'username' => $follower->getViewer()->getFirstName(),
                            'authorFullName' => $author->getFullName(),
                            'videoProjectTitle' => $videoProjectMessage->getVideoProject()->getTitle(),
                            'videoProjectUrl' => $this->router->generate('front_coachingvideo_videoproject_view', [
                                'videoProjectId' => $videoProjectMessage->getVideoProject()->getId(),
                                '_fragment' => 'videoproject-discussion-target'
                            ], UrlGeneratorInterface::ABSOLUTE_URL)
                        ],
                        new EmailRecipientList([$this->createUserEmailContact($follower->getViewer())]),
                        [],
                        $author->getFullName()
                    );
                }
            }
        }
    }
}