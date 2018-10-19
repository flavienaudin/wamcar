<?php

namespace AppBundle\MailWorkflow;


use AppBundle\MailWorkflow\Model\EmailContact;
use AppBundle\MailWorkflow\Model\EmailRecipientList;
use Wamcar\User\Event\EmailsInvitationsEvent;
use Wamcar\User\Event\InvitationsEvent;
use Wamcar\User\Event\InvitationsEventHandler;

class EmailsInvitationsEventHandler extends AbstractEmailEventHandler implements InvitationsEventHandler
{
    /**
     * @inheritDoc
     */
    public function notify(InvitationsEvent $invitationsEvent)
    {
        $this->checkEventClass($invitationsEvent, EmailsInvitationsEvent::class);
        /** @var EmailsInvitationsEvent $invitationsEvent */
        $garage = $invitationsEvent->getGarage();
        $administratorFullname = count($garage->getAdministrators()>0)?$garage->getAdministrators()[0]->getFullName():'On';

        $emailContactsList = [];
        foreach ($invitationsEvent->getInvitations() as $emailInvitation) {
            $emailContactsList[] = new EmailContact($emailInvitation);
        }

        $this->send(
            $this->translator->trans('inviteByEmailToRegister.object', [
                '%administrator_fullname%' => $administratorFullname
            ], 'email'),
            'Mail/inviteByEmailToRegister.html.twig',
            [
                'administrator_fullname' => $administratorFullname,
                'garage' => $garage
            ],
            new EmailRecipientList($emailContactsList)
        );

    }
}