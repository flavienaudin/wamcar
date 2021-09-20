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
use Wamcar\VideoCoaching\Event\VideoProjectShareEvent;
use Wamcar\VideoCoaching\Event\VideoProjectShareEventHandler;
use Wamcar\VideoCoaching\Event\VideoProjectSharingSuccessEvent;
use Wamcar\VideoCoaching\VideoProject;
use Wamcar\VideoCoaching\VideoProjectViewer;

class NotifyNewFollowersOfProjectVideoSharingSuccess extends AbstractEmailEventHandler implements VideoProjectShareEventHandler
{

    /** @var NotificationManager $notificationsManager */
    protected $notificationsManager;
    /** @var NotificationManagerExtended $notificationsManagerExtended */
    protected $notificationsManagerExtended;

    /**
     * NotifyProUserOfProjectVideoSharingEventHandler constructor.
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
     * @param VideoProjectShareEvent $event
     */
    public function notify(VideoProjectShareEvent $event)
    {
        $this->checkEventClass($event, VideoProjectSharingSuccessEvent::class);

        /** @var VideoProject $videoProject */
        $videoProject = $event->getVideoProject();
        $notificationData = json_encode(['id' => $videoProject->getId()]);
        /** @var array $followers */
        $followers = $event->getFollowers();

        /** @var string $email */
        /** @var VideoProjectViewer $follower */
        foreach ($followers as $email => $follower) {
            // Create notification
            $notifications = $this->notificationsManagerExtended->createNotification(
                get_class($videoProject),
                get_class($event),
                $notificationData,
                $this->router->generate('front_coachingvideo_videoproject_view', ['videoProjectId' => $videoProject->getId()])
            );
            try {
                $this->notificationsManager->addNotification([$follower->getViewer()], $notifications, true);
            } catch (OptimisticLockException $e) {
                // tant pis pour la notification, on ne bloque pas l'action
            }

            // Send email according to user preference
            if ($follower->getViewer()->getPreferences()->isVideoProjectSharingEmailEnabled() &&
                NotificationFrequency::IMMEDIATELY()->equals($follower->getViewer()->getPreferences()->getVideoProjectSharingEmailFrequency())
            ) {
                $creatorFullName = $videoProject->getCreators()->first()->getViewer()->getFullName();
                $emailObject = $this->translator->trans('notifyNewFollowersOfVideoProjectSharingSuccess.object', [
                    '%authorFullName%' => $creatorFullName,
                    '%videoProjectTitle%' => $videoProject->getTitle()
                ], 'email');

                $trackingKeywords = 'videoProject' . $videoProject->getId() . '-follower' . $follower->getViewer()->getId();

                $commonUTM = [
                    'utm_source' => 'notifications',
                    'utm_medium' => 'email',
                    'utm_campaign' => 'video_project_sharing_success',
                    'utm_term' => $trackingKeywords
                ];

                $this->send(
                    $emailObject,
                    'Mail/notifyNewFollowersOfVideoProjectSharingSuccess.html.twig',
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
                        'authorFullName' => $creatorFullName,
                        'videoProjectTitle' => $videoProject->getTitle(),
                        'videoProjectUrl' => $this->router->generate('front_coachingvideo_videoproject_view', [
                            'videoProjectId' => $videoProject->getId()
                        ], UrlGeneratorInterface::ABSOLUTE_URL)
                    ],
                    new EmailRecipientList([$this->createUserEmailContact($follower->getViewer())]),
                    [],
                    $creatorFullName
                );
            }
        }
    }
}