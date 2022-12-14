<?php


namespace AppBundle\MailWorkflow;


use AppBundle\MailWorkflow\Model\EmailRecipientList;
use AppBundle\MailWorkflow\Services\Mailer;
use AppBundle\Services\Picture\PathVehiclePicture;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Wamcar\Conversation\Event\ProContactMessageCreated;
use Wamcar\Conversation\Event\ProContactMessageEvent;
use Wamcar\Conversation\Event\ProContactMessageEventHandler;
use Wamcar\Conversation\ProContactMessage;
use Wamcar\Vehicle\PersonalVehicle;
use Wamcar\Vehicle\ProVehicle;

class NotifyProUserOfContactMessageCreated extends AbstractEmailEventHandler implements ProContactMessageEventHandler
{

    /** @var PathVehiclePicture */
    private $pathVehiclePicture;

    /**
     * AbstractEmailEventHandler constructor.
     * @param Mailer $mailer
     * @param UrlGeneratorInterface $router
     * @param EngineInterface $templating
     * @param TranslatorInterface $translator
     * @param string $type
     * @param PathVehiclePicture $pathVehiclePicture
     */
    public function __construct(
        Mailer $mailer,
        UrlGeneratorInterface $router,
        EngineInterface $templating,
        TranslatorInterface $translator,
        string $type,
        PathVehiclePicture $pathVehiclePicture
    )
    {
        parent::__construct($mailer, $router, $templating, $translator, $type);
        $this->pathVehiclePicture = $pathVehiclePicture;
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

        /*if ($proUser->getPreferences()->isPrivateMessageEmailEnabled() &&
            NotificationFrequency::IMMEDIATELY()->equals($proUser->getPreferences()->getGlobalEmailFrequency())
            // Use only the global email frequency preference
            // && $interlocutor->getPreferences()->getPrivateMessageEmailFrequency()->getValue() === NotificationFrequency::IMMEDIATELY
        ) {*/
        if ($proContactMessage->getCreatedAt() != null) {
            $emailObject = $this->translator->trans('notifyProUserOfContactMessageCreated.object.vehicle', [], 'email');
        } else {
            $emailObject = $this->translator->trans('notifyProUserOfContactMessageCreated.object.profile', [
                '%proContactMessageAuthorName%' => $contactFullName
            ], 'email');
        }
        $trackingKeywords = 'advisor' . $proUser->getId();

        $commonUTM = [
            'utm_source' => 'notifications',
            'utm_medium' => 'email',
            'utm_campaign' => 'pro_contact',
            'utm_term' => $trackingKeywords
        ];
        $pathImg = null;
        $vehicleUrl = null;
        $vehiclePrice = null;
        if ($proContactMessage->getVehicle()) {
            $pathImg = $this->pathVehiclePicture->getPath($proContactMessage->getVehicle()->getMainPicture(), 'vehicle_mini_thumbnail');

            if ($proContactMessage->getVehicle() instanceof ProVehicle) {
                $vehicleUrl = $this->router->generate(
                    "front_vehicle_pro_detail",
                    array_merge($commonUTM, [
                        'slug' => $proContactMessage->getVehicle()->getSlug(),
                        'utm_content' => 'vehicle'
                    ]),
                    UrlGeneratorInterface::ABSOLUTE_URL);
                $vehiclePrice = $proContactMessage->getVehicle()->getPrice();
            } elseif ($proContactMessage->getVehicle() instanceof PersonalVehicle) {
                $vehicleUrl = $this->router->generate("front_vehicle_personal_detail", array_merge(
                    $commonUTM, [
                    'slug' => $proContactMessage->getVehicle()->getSlug(),
                    'utm_content' => 'vehicle',
                ]), UrlGeneratorInterface::ABSOLUTE_URL);
            }
        }
        $this->send(
            $emailObject,
            'Mail/notifyProUserOfContactMessageCreated.html.twig',
            [
                'common_utm' => $commonUTM,
                'transparentPixel' => [
                    'tid' => 'UA-73946027-1',
                    'cid' => $proUser->getUserID(),
                    't' => 'event',
                    'ec' => 'email',
                    'ea' => 'open',
                    'el' => urlencode($emailObject),
                    'dh' => $this->router->getContext()->getHost(),
                    'dp' => urlencode('/email/procontact/open/' . $proContactMessage->getId()),
                    'dt' => urlencode($emailObject),
                    'cs' => 'notifications', // Campaign source
                    'cm' => 'email', // Campaign medium
                    'cn' => 'procontact', // Campaign name
                    'ck' => $trackingKeywords, // Campaign Keyword (/ terms)
                    'cc' => 'opened', // Campaign content
                ],
                'username' => $proUser->getFirstName(),
                'contactFullName' => $contactFullName,
                'contactEmail' => $proContactMessage->getEmail(),
                'contactPhonenumber' => $proContactMessage->getPhonenumber(),
                'message' => $proContactMessage->getMessage(),
                'vehicle' => $proContactMessage->getVehicle(),
                'vehicleUrl' => $vehicleUrl,
                'vehiclePrice' => $vehiclePrice,
                'thumbnailUrl' => $pathImg
            ],
            new EmailRecipientList([$this->createUserEmailContact($proUser)]),
            [],
            $proContactMessage->getFirstName()
        );
        //}
    }
}