<?php


namespace AppBundle\MailWorkflow;


use AppBundle\MailWorkflow\Model\EmailRecipientList;
use AppBundle\MailWorkflow\Services\Mailer;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Wamcar\Conversation\Event\MessageCreated;
use Wamcar\Conversation\Event\MessageEvent;
use Wamcar\Conversation\Event\MessageEventHandler;
use Wamcar\Conversation\Message;
use Wamcar\User\BaseUser;
use Wamcar\Vehicle\Enum\NotificationFrequency;
use Wamcar\Vehicle\PersonalVehicle;
use Wamcar\Vehicle\ProVehicle;


class NotifyUserOfMessageCreated extends AbstractEmailEventHandler implements MessageEventHandler
{
    /**
     * AbstractEmailEventHandler constructor.
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
     * @param MessageEvent $event
     */
    public function notify(MessageEvent $event)
    {
        $this->checkEventClass($event, MessageCreated::class);

        /** @var Message $message */
        $message = $event->getMessage();
        $interlocutor = $event->getInterlocutor();
        /** @var BaseUser $sender */
        $sender = $message->getUser();
        if ($interlocutor->getPreferences()->isPrivateMessageEmailEnabled() &&
            NotificationFrequency::IMMEDIATELY()->equals($interlocutor->getPreferences()->getGlobalEmailFrequency())
            // Use only the global email frequency preference
            // && $interlocutor->getPreferences()->getPrivateMessageEmailFrequency()->getValue() === NotificationFrequency::IMMEDIATELY
        ) {
            $pathImg = $event->getPathImg();

            $emailObject = $this->translator->trans('notifyUserOfMessageCreated.object', ['%messageAuthorName%' => $sender->getFullName()], 'email');
            $trackingKeywords = ($interlocutor->isPro() ? 'advisor' : 'customer') . $interlocutor->getId() . '_' . ($sender->isPro() ? 'advisor' : 'customer') . $sender->getId();

            $commonUTM = [
                'utm_source' => 'notifications',
                'utm_medium' => 'email',
                'utm_campaign' => 'mp',
                'utm_term' => $trackingKeywords
            ];

            $this->send(
                $emailObject,
                'Mail/notifyUserOfMessageCreated.html.twig',
                [
                    'common_utm' => $commonUTM,
                    'transparentPixel' => [
                        'tid' => 'UA-73946027-1',
                        'cid' => $interlocutor->getUserID(),
                        't' => 'event',
                        'ec' => 'email',
                        'ea' => 'open',
                        'el' => urlencode($emailObject),
                        'dh' => $this->router->getContext()->getHost(),
                        'dp' => urlencode('/email/mp/open/' . $message->getId()),
                        'dt' => urlencode($emailObject),
                        'cs' => 'notifications', // Campaign source
                        'cm' => 'email', // Campaign medium
                        'cn' => 'mp', // Campaign name
                        'ck' => $trackingKeywords, // Campaign Keyword (/ terms)
                        'cc' => 'opened', // Campaign content
                    ],
                    'username' => $interlocutor->getFirstName(),
                    'sender' => $sender,
                    'message' => $message->getContent(),
                    'message_attachments' => $message->getAttachments(),
                    'message_url' => $this->router->generate("front_conversation_edit", array_merge(
                        $commonUTM,
                        [
                            'id' => $message->getConversation()->getId(),
                            '_fragment' => 'last-message',
                            'utm_content' => 'button_answer'
                        ]), UrlGeneratorInterface::ABSOLUTE_URL),
                    'vehicle' => $message->getVehicle(),
                    'vehicleUrl' => $message->getVehicle() instanceof ProVehicle ?
                        $this->router->generate("front_vehicle_pro_detail", array_merge(
                            $commonUTM, [
                            'slug' => $message->getVehicle()->getSlug(),
                            'utm_content' => 'vehicle'
                        ]), UrlGeneratorInterface::ABSOLUTE_URL)
                        : $message->getVehicle() instanceof PersonalVehicle ?
                            $this->router->generate("front_vehicle_personal_detail", array_merge(
                                $commonUTM, [
                                'slug' => $message->getVehicle()->getSlug(),
                                'utm_content' => 'vehicle',
                            ]), UrlGeneratorInterface::ABSOLUTE_URL)
                            : null,
                    'vehiclePrice' => ($message->getVehicle() instanceof ProVehicle ? $message->getVehicle()->getPrice() : null),
                    'thumbnailUrl' => $pathImg
                ],
                new EmailRecipientList([$this->createUserEmailContact($interlocutor)]),
                [],
                $message->getUser()->getFirstName()
            );
        }
    }
}
