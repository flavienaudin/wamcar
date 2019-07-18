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

        $trackingKeywords = ($user->isPro() ? 'advisor' : 'customer') . $user->getId();
        $commonUTM = [
            'utm_source' => 'notifications',
            'utm_medium' => 'email',
            'utm_campaign' => 'wamaffinity_ready',
            'utm_term' => $trackingKeywords
        ];
        $this->send(
            $this->translator->trans('notifyUserOfAffinityDegreeCalculated.object', [], 'email'),
            'Mail/notifyUserOfAffinityDegreeCalculated.html.twig',
            [
                'common_utm' => $commonUTM,
                'username' => $user->getFirstName(),
                'user' => $user
            ],
            new EmailRecipientList([$this->createUserEmailContact($user)])
        );
    }
}