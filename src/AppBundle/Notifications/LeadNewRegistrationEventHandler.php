<?php


namespace AppBundle\Notifications;


use AppBundle\MailWorkflow\AbstractEmailEventHandler;
use AppBundle\MailWorkflow\Model\EmailRecipientList;
use AppBundle\MailWorkflow\Services\Mailer;
use AppBundle\Services\Notification\NotificationManagerExtended;
use Doctrine\ORM\OptimisticLockException;
use Mgilet\NotificationBundle\Manager\NotificationManager;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Wamcar\User\Event\LeadEvent;
use Wamcar\User\Event\LeadEventHandler;
use Wamcar\User\Event\LeadNewRegistrationEvent;
use Wamcar\User\ProUser;

class LeadNewRegistrationEventHandler extends AbstractEmailEventHandler implements LeadEventHandler
{

    /** @var NotificationManager $notificationsManager */
    protected $notificationsManager;
    /** @var NotificationManagerExtended $notificationsManagerExtended */
    protected $notificationsManagerExtended;

    /**
     * LikeNotificationsHandler constructor.
     * @param Mailer $mailer
     * @param UrlGeneratorInterface $router
     * @param EngineInterface $templating
     * @param TranslatorInterface $translator
     * @param string $type
     * @param NotificationManager $notificationsManager
     * @param NotificationManagerExtended $notificationsManagerExtended
     */
    public function __construct(Mailer $mailer, UrlGeneratorInterface $router, EngineInterface $templating, TranslatorInterface $translator, string $type, NotificationManager $notificationsManager, NotificationManagerExtended $notificationsManagerExtended)
    {
        parent::__construct($mailer, $router, $templating, $translator, $type);
        $this->notificationsManager = $notificationsManager;
        $this->notificationsManagerExtended = $notificationsManagerExtended;
    }

    /**
     * @param LeadEvent $event
     */
    public function notify(LeadEvent $event)
    {
        $this->checkEventClass($event, LeadNewRegistrationEvent::class);

        $proUser = $event->getLeadOwner();
        $leadUser = $event->getLeadUser();
        if (!$proUser->is($leadUser)) {
            // LeadUser <> LeadOnwer : ne doit pas arriver

            // Création de la notification
            $data = json_encode(['id' => $leadUser->getId()]);
            $existingNotification = null;
            try {
                // security check : if the notification is already existing about the new lead
                $existingNotification = $this->notificationsManagerExtended->getNotificationByObjectDescriptionAndNotifiable([
                    'subject' => get_class($leadUser),
                    'message' => $data
                ], $this->notificationsManager->getNotifiableEntity($proUser));
            } catch (OptimisticLockException $e) {
                // tant pis pour la notification, on ne bloque pas l'action
            }
            if (empty($existingNotification)) {
                $notifications = $this->notificationsManagerExtended->createNotification(
                    get_class($leadUser),
                    get_class($event),
                    $data,
                    $leadUser instanceof ProUser ?
                        $this->router->generate('front_view_pro_user_info', ['slug' => $leadUser->getSlug()])
                        : $this->router->generate('front_view_personal_user_info', ['slug' => $leadUser->getSlug()])
                );
                try {
                    $this->notificationsManager->addNotification([$proUser], $notifications, true);
                } catch (OptimisticLockException $e) {
                    // tant pis pour la notification, on ne bloque pas l'action
                }

                // Envoi du e-mail selon la préférence
                if ($proUser->getPreferences()->isLeadEmailEnabled()) {
                    $this->send(
                        $this->translator->trans('notifyProUserOfNewInterestingLead.object', [], 'email'),
                        'Mail/notifyProUserOfNewInterestingLead.html.twig',
                        [
                            'username' => $proUser->getFirstName(),
                            'leadFullname' => $leadUser->getFullName(),
                            'prefradius' => $proUser->getPreferences()->getLeadLocalizationRadiusCriteria(),
                            'profile_url' => $leadUser instanceof ProUser ?
                                $this->router->generate('front_view_pro_user_info', ['slug' => $leadUser->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL)
                                : $this->router->generate('front_view_personal_user_info', ['slug' => $leadUser->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL)
                        ],
                        new EmailRecipientList($this->createUserEmailContact($proUser))
                    );
                }
            }
        }
    }
}