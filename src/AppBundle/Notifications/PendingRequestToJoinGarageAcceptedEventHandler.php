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
use Wamcar\Garage\Event\GarageMemberManagementEvent;
use Wamcar\Garage\Event\GarageMemberManagementEventHandler;
use Wamcar\Garage\Event\PendingRequestToJoinGarageAcceptedEvent;

class PendingRequestToJoinGarageAcceptedEventHandler extends AbstractEmailEventHandler implements GarageMemberManagementEventHandler
{
    /** @var NotificationManager $notificationsManager */
    protected $notificationsManager;
    /** @var NotificationManagerExtended $notificationsManagerExtended */
    protected $notificationsManagerExtended;

    /**
     * @inheritDoc
     */
    public function __construct(Mailer $mailer, UrlGeneratorInterface $router, EngineInterface $templating, TranslatorInterface $translator, string $type, NotificationManager $notificationsManager, NotificationManagerExtended $notificationsManagerExtended)
    {
        parent::__construct($mailer, $router, $templating, $translator, $type);
        $this->notificationsManager = $notificationsManager;
        $this->notificationsManagerExtended = $notificationsManagerExtended;
    }


    public function notify(GarageMemberManagementEvent $event)
    {
        $this->checkEventClass($event, PendingRequestToJoinGarageAcceptedEvent::class);

        $garage = $event->getGarageProUser()->getGarage();
        $proUser = $event->getGarageProUser()->getProUser();

        // Deletion of the administrators' notifications
        $data = [
            'garage' => $garage->getId(),
            'proUser' => $proUser->getId()
        ];
        $notifications = $this->notificationsManagerExtended->getNotificationByObjectDescription([
            'subject' => get_class($event->getGarageProUser()),
            'message' => json_encode($data)
        ]);
        try {
            foreach ($notifications as $notification) {
                $this->notificationsManager->removeNotification(array_merge([$proUser], $garage->getAdministrators()), $notification);
                $this->notificationsManager->deleteNotification($notification, true);
            }
        } catch (OptimisticLockException $e) {
            // tant pis pour la suppression des notifications, on ne bloque pas l'action
        }

        // Creation of the notification to seller
        try {
            $data = [
                'garage' => $garage->getId(),
                'proUser' => $proUser->getId()
            ];

            $notification = $this->notificationsManagerExtended->createNotification(
                get_class($event->getGarageProUser()),
                get_class($event),
                json_encode($data),
                $this->router->generate('front_garage_view', [
                    'id' => $garage->getId(), '_fragment' => 'sellers'])
            );

            $this->notificationsManager->addNotification([$proUser], $notification, true);
        } catch (OptimisticLockException $e) {
            // tant pis pour la notification, on ne bloque pas l'action
        }

        $this->send(
            $this->translator->trans('notifyProOfAcceptedPendingRequestToJoinGarage.object', [
                '%seller_fullname%' => $proUser->getFullName(),
                '%garage_name%' => $garage->getName()
            ], 'email'),
            'Mail/notifyProOfAcceptedPendingRequestToJoinGarage.html.twig',
            [
                'username' => $proUser->getFirstName(),
                'garage' => $garage,
                'seller' => $proUser
            ],
            new EmailRecipientList($this->createUserEmailContact($proUser))
        );
    }

}