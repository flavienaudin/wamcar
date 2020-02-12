<?php


namespace AppBundle\MailWorkflow;


use AppBundle\MailWorkflow\Model\EmailContact;
use AppBundle\MailWorkflow\Model\EmailRecipientList;
use AppBundle\MailWorkflow\Services\Mailer;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Wamcar\Conversation\Event\ProContactMessageCreated;
use Wamcar\Conversation\Event\ProContactMessageEvent;
use Wamcar\Conversation\Event\ProContactMessageEventHandler;
use Wamcar\Conversation\ProContactMessage;

class EncourageToRegisterAfterProContact extends AbstractEmailEventHandler implements ProContactMessageEventHandler
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
     * @param ProContactMessageEvent $event
     */
    public function notify(ProContactMessageEvent $event)
    {
        $this->checkEventClass($event, ProContactMessageCreated::class);

        /** @var ProContactMessage $proContactMessage */
        $proContactMessage = $event->getProContactMessage();
        $proUser = $proContactMessage->getProUser();
        $contactFullName = $proContactMessage->getFirstname() . ' ' . $proContactMessage->getLastname();

        $emailObject = $this->translator->trans('encourageToRegisterAfterProContact.object', [], 'email');
        $trackingKeywords = 'visitor_'.$proContactMessage->getId();

        $commonUTM = [
            'utm_source' => 'notifications',
            'utm_medium' => 'email',
            'utm_campaign' => 'encourage_registration',
            'utm_term' => $trackingKeywords
        ];

        $this->send(
            $emailObject,
            'Mail/encourageToRegisterAfterProContact.html.twig',
            [
                'common_utm' => $commonUTM,
                'transparentPixel' => [
                    'tid' => 'UA-73946027-1',
                    'cid' => $proContactMessage->getId(),
                    't' => 'event',
                    'ec' => 'email',
                    'ea' => 'open',
                    'el' => urlencode($emailObject),
                    'dh' => $this->router->getContext()->getHost(),
                    'dp' => urlencode('/email/encourage_registration/open/' . $proContactMessage->getId()),
                    'dt' => urlencode($emailObject),
                    'cs' => 'notifications', // Campaign source
                    'cm' => 'email', // Campaign medium
                    'cn' => 'encourage_registration', // Campaign name
                    'ck' => $trackingKeywords, // Campaign Keyword (/ terms)
                    'cc' => 'opened', // Campaign content
                ],
                'username' => $contactFullName,
                'contactEmail' => $proContactMessage->getEmail(),
                'contactPhonenumber' => $proContactMessage->getPhonenumber(),
                'contactedProUser' => $proUser,
                'managePrefDisabled'=> true,
                'vehicle' => $proContactMessage->getVehicle(),
                'proUser' => $proUser
            ],
            new EmailRecipientList([new EmailContact($proContactMessage->getEmail(), $contactFullName)]),
            []
        );
    }
}