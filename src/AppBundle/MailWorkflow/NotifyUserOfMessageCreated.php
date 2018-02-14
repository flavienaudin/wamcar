<?php


namespace AppBundle\MailWorkflow;


use AppBundle\Doctrine\Repository\DoctrineConversationUserRepository;
use AppBundle\MailWorkflow\Model\EmailRecipientList;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Wamcar\Conversation\Event\MessageCreated;
use Wamcar\Conversation\Message;
use Wamcar\Message\Event\MessageEvent;
use Wamcar\Message\Event\MessageEventHandler;

class NotifyUserOfMessageCreated extends AbstractEmailEventHandler implements MessageEventHandler
{
    /** @var DoctrineConversationUserRepository */
    protected $conversationUserRepository;

    /**
     * @param MessageEvent $event
     */
    public function notify(MessageEvent $event)
    {
        $this->checkEventClass($event, MessageCreated::class);

        /** @var Message $message */
        $message = $event->getMessage();
        $interlocutorConversation = $this->conversationUserRepository->findInterlocutorConversation($message->getConversation(), $message->getUser());

        $this->send(
            $this->translator->trans('notifyUserOfMessageCreated.title', [], 'email'),
            'Mail/notifyUserOfMessageCreated.html.twig',
            [
                'siteUrl' => $this->router->generate('front_default', [], UrlGeneratorInterface::ABSOLUTE_URL)
            ],
            new EmailRecipientList($interlocutorConversation->getUser())
        );
    }
}
