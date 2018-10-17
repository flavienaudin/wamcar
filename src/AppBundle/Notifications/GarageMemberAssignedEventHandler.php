<?php

namespace AppBundle\Notifications;

use AppBundle\MailWorkflow\AbstractEmailEventHandler;
use AppBundle\MailWorkflow\Services\Mailer;
use AppBundle\Services\Notification\NotificationManagerExtended;
use Doctrine\ORM\OptimisticLockException;
use Mgilet\NotificationBundle\Manager\NotificationManager;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Wamcar\Garage\Event\GarageMemberAssignedEvent;
use Wamcar\Garage\Event\GarageMemberManagementEvent;
use Wamcar\Garage\Event\GarageMemberManagementEventHandler;


class GarageMemberAssignedEventHandler extends AbstractEmailEventHandler implements GarageMemberManagementEventHandler
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
        $this->checkEventClass($event, GarageMemberAssignedEvent::class);

        $garage = $event->getGarageProUser()->getGarage();
        $proUser = $event->getGarageProUser()->getProUser();

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

        /* TODO Send an email to seller ?
        foreach ($garage->getAdministrators() as $administrator) {
            $this->send(
                $this->translator->trans('notifyGarageAdministratorOfNewPendingRequest.object', [
                    '%garage_name%' => $garage->getName()], 'email'),
                'Mail/notifyGarageAdministratorOfNewPendingRequest.html.twig',
                [
                    'username' => $administrator->getFullName(),
                    'garage' => $garage,
                    'seller' => $proUser
                ],
                new EmailRecipientList($this->createUserEmailContact($administrator))
            );
        }*/
    }

}