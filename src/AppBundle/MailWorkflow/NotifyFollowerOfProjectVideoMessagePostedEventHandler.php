<?php


namespace AppBundle\MailWorkflow;


use AppBundle\MailWorkflow\Model\EmailRecipientList;
use AppBundle\MailWorkflow\Services\Mailer;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Wamcar\VideoCoaching\Event\VideoProjectMessageEvent;
use Wamcar\VideoCoaching\Event\VideoProjectMessageEventHandler;
use Wamcar\VideoCoaching\Event\VideoProjectMessagePostedEvent;
use Wamcar\VideoCoaching\VideoProjectMessage;
use Wamcar\VideoCoaching\VideoProjectViewer;

class NotifyFollowerOfProjectVideoMessagePostedEventHandler extends AbstractEmailEventHandler implements VideoProjectMessageEventHandler
{


    /**
     * NotifyFollowerOfProjectVideoMessagePostedEventHandler constructor.
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
     * @param VideoProjectMessageEvent $event
     */
    public function notify(VideoProjectMessageEvent $event)
    {
        $this->checkEventClass($event, VideoProjectMessagePostedEvent::class);

        /** @var VideoProjectMessage $videoProjectMessage */
        $videoProjectMessage = $event->getVideoProjectMessage();
        $author = $videoProjectMessage->getAuthor();
        $followers = $videoProjectMessage->getVideoProject()->getViewers(true);
        /** @var VideoProjectViewer $follower */
        foreach ($followers as $follower) {

            /*if ($author->getPreferences()->isPrivateMessageEmailEnabled() &&
                NotificationFrequency::IMMEDIATELY()->equals($author->getPreferences()->getGlobalEmailFrequency())
                // Use only the global email frequency preference
                // && $interlocutor->getPreferences()->getPrivateMessageEmailFrequency()->getValue() === NotificationFrequency::IMMEDIATELY
            ) {*/

            $emailObject = $this->translator->trans('notifyFollowersOfVideoProjectMessagePosted.object', [
                '%authorFullName%' => $author->getFullName(),
                '%videoProjectTitle%' => $videoProjectMessage->getVideoProject()->getTitle()
            ], 'email');

            $trackingKeywords = 'videoProject' . $videoProjectMessage->getVideoProject()->getId().'-follower'. $follower->getViewer()->getId();

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
                        'id' => $videoProjectMessage->getVideoProject()->getId()
                    ], UrlGeneratorInterface::ABSOLUTE_URL)
                ],
                new EmailRecipientList([$this->createUserEmailContact($follower->getViewer())]),
                [],
                $author->getFullName()
            );
            //}
        }
    }
}