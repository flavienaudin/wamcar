<?php

namespace AppBundle\MailWorkflow;


use AppBundle\MailWorkflow\Model\EmailRecipientList;
use Wamcar\User\BaseUser;
use Wamcar\User\Event\AffinityDegreeCalculatedEvent;
use Wamcar\User\Event\UserEvent;
use Wamcar\User\Event\UserEventHandler;

class NotifyUserOfAffinityDegreeCalculated extends AbstractEmailEventHandler implements UserEventHandler
{

    /**
     * @param UserEvent $event
     */
    public function notify(UserEvent $event)
    {
        $this->checkEventClass($event, AffinityDegreeCalculatedEvent::class);

        /** @var BaseUser $user */
        $user = $event->getUser();

        $this->send(
            $this->translator->trans('notifyUserOfAffinityDegreeCalculated.object', [], 'email'),
            'Mail/notifyUserOfAffinityDegreeCalculated.html.twig',
            [
                'username' => $user->getFirstName(),
                'user' => $user
            ],
            new EmailRecipientList([$this->createUserEmailContact($user)])
        );
    }
}