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
        if ($interlocutor->getPreferences()->isPrivateMessageEmailEnabled()
            && $interlocutor->getPreferences()->getPrivateMessageEmailFrequency()->getValue() === NotificationFrequency::IMMEDIATELY) {
            $pathImg = $event->getPathImg();

            $this->send(
                $this->translator->trans('notifyUserOfMessageCreated.object', ['%messageAuthorName%' => $message->getUser()->getFullName()], 'email'),
                'Mail/notifyUserOfMessageCreated.html.twig',
                [
                    'username' => $interlocutor->getFirstName(),
                    'sender' => $message->getUser(),
                    'message' => $message->getContent(),
                    'message_attachments' => $message->getAttachments(),
                    'message_url' => $this->router->generate("front_conversation_edit", ['id' => $message->getConversation()->getId(), '_fragment' => 'last-message'], UrlGeneratorInterface::ABSOLUTE_URL),
                    'vehicle' => $message->getVehicle(),
                    'vehicleUrl' => $message->getVehicle() instanceof ProVehicle ?
                        $this->router->generate("front_vehicle_pro_detail", ['slug' => $message->getVehicle()->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL)
                        : $message->getVehicle() instanceof PersonalVehicle ? $this->router->generate("front_vehicle_personal_detail", ['slug' => $message->getVehicle()->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL) : null,

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
