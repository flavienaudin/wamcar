<?php

namespace AppBundle\MailWorkflow;


use AppBundle\MailWorkflow\Model\EmailRecipientList;
use GoogleApi\Event\GoogleApiReturnErrorEvent;
use GoogleApi\Event\GoogleApiReturnErrorEventHandler;
use GoogleApi\Event\PlaceDetailError;

class NotifyAdminOfGoogleApiReturnError extends AbstractEmailEventHandler implements GoogleApiReturnErrorEventHandler
{

    public function notify(GoogleApiReturnErrorEvent $event)
    {
        $this->checkEventClass($event, PlaceDetailError::class);

        $event->getCallParams();
        $this->send(
            $this->translator->trans('notifyAdminOfGoogleApiReturn.object', [], 'email'),
            'Mail/notifyAdminOfGoogleApiReturnError.html.twig',
            [
                'status' => $event->getReturnStatus(),
                'message' => $event->getMessage(),
                'callParams' => $event->getCallParams()
            ],
            new EmailRecipientList(['wamcartest@gmail.com'])
        );

    }
}