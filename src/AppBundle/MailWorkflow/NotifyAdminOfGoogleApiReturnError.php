<?php

namespace AppBundle\MailWorkflow;


use AppBundle\MailWorkflow\Model\EmailContact;
use AppBundle\MailWorkflow\Model\EmailRecipientList;
use AppBundle\MailWorkflow\Services\Mailer;
use GoogleApi\Event\GoogleApiReturnErrorEvent;
use GoogleApi\Event\GoogleApiReturnErrorEventHandler;
use GoogleApi\Event\PlaceDetailError;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Translation\TranslatorInterface;

class NotifyAdminOfGoogleApiReturnError extends AbstractEmailEventHandler implements GoogleApiReturnErrorEventHandler
{
    /** @var array */
    private $monitorsEmails;

    /**
     * NotifyAdminOfGoogleApiReturnError constructor.
     * @param Mailer $mailer
     * @param UrlGeneratorInterface $router
     * @param EngineInterface $templating
     * @param TranslatorInterface $translator
     * @param string $type
     * @param array $monitorsEmails
     */
    public function __construct(Mailer $mailer, UrlGeneratorInterface $router, EngineInterface $templating, TranslatorInterface $translator, string $type, array $monitorsEmails)
    {
        parent::__construct($mailer, $router, $templating, $translator, $type);;
        $this->monitorsEmails = [];
        foreach ($monitorsEmails as $monitorsEmail) {
            $this->monitorsEmails[] = new EmailContact($monitorsEmail);
        }
    }

    public function notify(GoogleApiReturnErrorEvent $event)
    {
        $this->checkEventClass($event, PlaceDetailError::class);

        $this->send(
            $this->translator->trans('notifyAdminOfGoogleApiReturn.object', [], 'email'),
            'Mail/notifyAdminOfGoogleApiReturnError.html.twig',
            [
                'status' => $event->getReturnStatus(),
                'message' => $event->getMessage(),
                'callParams' => $event->getCallParams()
            ],

            new EmailRecipientList($this->monitorsEmails)
        );
    }
}