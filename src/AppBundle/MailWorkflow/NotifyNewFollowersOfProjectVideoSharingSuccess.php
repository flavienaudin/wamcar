<?php


namespace AppBundle\MailWorkflow;


use AppBundle\MailWorkflow\Model\EmailRecipientList;
use AppBundle\MailWorkflow\Services\Mailer;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Wamcar\VideoCoaching\Event\VideoProjectShareEvent;
use Wamcar\VideoCoaching\Event\VideoProjectShareEventHandler;
use Wamcar\VideoCoaching\Event\VideoProjectSharingSuccessEvent;
use Wamcar\VideoCoaching\VideoProject;
use Wamcar\VideoCoaching\VideoProjectViewer;

class NotifyNewFollowersOfProjectVideoSharingSuccess extends AbstractEmailEventHandler implements VideoProjectShareEventHandler
{

    /**
     * NotifyProUserOfProjectVideoSharingEventHandler constructor.
     * @param Mailer $mailer
     * @param UrlGeneratorInterface $router
     * @param EngineInterface $templating
     * @param TranslatorInterface $translator
     * @param string $type
     */
    public function __construct(
        Mailer $mailer,
        UrlGeneratorInterface $router,
        EngineInterface $templating,
        TranslatorInterface $translator,
        string $type
    )
    {
        parent::__construct($mailer, $router, $templating, $translator, $type);
    }

    /**
     * @param VideoProjectShareEvent $event
     */
    public function notify(VideoProjectShareEvent $event)
    {
        $this->checkEventClass($event, VideoProjectSharingSuccessEvent::class);

        /** @var VideoProject $videoProject */
        $videoProject = $event->getVideoProject();
        /** @var array $followers */
        $followers = $event->getFollowers();

        /** @var string $email */
        /** @var VideoProjectViewer $follower */
        foreach ($followers as $email => $follower) {

            /*if ($author->getPreferences()->isPrivateMessageEmailEnabled() &&
                NotificationFrequency::IMMEDIATELY()->equals($author->getPreferences()->getGlobalEmailFrequency())
                // Use only the global email frequency preference
                // && $interlocutor->getPreferences()->getPrivateMessageEmailFrequency()->getValue() === NotificationFrequency::IMMEDIATELY
            ) {*/

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
                        'id' => $videoProject->getId()
                    ], UrlGeneratorInterface::ABSOLUTE_URL)
                ],
                new EmailRecipientList([$this->createUserEmailContact($follower->getViewer())]),
                [],
                $creatorFullName
            );
            //}
        }
    }
}