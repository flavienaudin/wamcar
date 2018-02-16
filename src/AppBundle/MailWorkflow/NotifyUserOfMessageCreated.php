<?php


namespace AppBundle\MailWorkflow;


use AppBundle\MailWorkflow\Model\EmailRecipientList;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
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
            $this->translator->trans('notifyUserOfMessageCreated.title', ['%interlocutorName%' =>  $interlocutor->getFullName()], 'email'),
            'Mail/notifyUserOfMessageCreated.html.twig',
            [
                'username' => $message->getUser()->getFullName(),
                'interlocutorName' => $interlocutor->getFullName(),
                'message' => $message->getContent(),
                'vehicle' => $message->getVehicle(),
                'pathImg' => $pathImg
            ],
            new EmailRecipientList([$this->createUserEmailContact($interlocutor)])
        );
    }
}
