<?php


namespace AppBundle\MailWorkflow;


use AppBundle\MailWorkflow\Model\EmailRecipientList;
use Wamcar\Conversation\Event\MessageCreated;
use Wamcar\Conversation\Event\MessageEvent;
use Wamcar\Conversation\Event\MessageEventHandler;
use Wamcar\Conversation\Message;

class NotifyUserOfMessageCreated extends AbstractEmailEventHandler implements MessageEventHandler
{

    /**
     * @param MessageEvent $event
     */
    public function notify(MessageEvent $event)
    {
        $this->checkEventClass($event, MessageCreated::class);

        /** @var Message $message */
        $message = $event->getMessage();
        $interlocutor = $event->getInterlocutor();
        $pathImg = $event->getPathImg();

        $this->send(
            $this->translator->trans('notifyUserOfMessageCreated.object', ['%messageAuthorName%' => $message->getUser()->getFullName()], 'email'),
            'Mail/notifyUserOfMessageCreated.html.twig',
            [
                'username' => $interlocutor->getFullName(),
                'messageAuthorName' => $message->getUser()->getFullName(),
                'message' => $message->getContent(),
                'vehicle' => $message->getVehicle(),
                'pathImg' => $pathImg
            ],
            new EmailRecipientList([$this->createUserEmailContact($interlocutor)])
        );
    }
}
