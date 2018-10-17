<?php

namespace AppBundle\Notifications;


use AppBundle\Doctrine\Entity\EventNotification;
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
use Wamcar\Garage\Event\PendingRequestToJoinGarageCreatedEvent;

class PendingRequestToJoinGarageCreatedEventHandler extends AbstractEmailEventHandler implements GarageMemberManagementEventHandler
{
    /** @var NotificationManager $notificationManager */
    protected $notificationManager;
    /** @var NotificationManagerExtended $notificationManagerExtended */
    protected $notificationManagerExtended;

    /**
     * @inheritDoc
     */
    public function __construct(Mailer $mailer, UrlGeneratorInterface $router, EngineInterface $templating, TranslatorInterface $translator, string $type, NotificationManager $notificationManager, NotificationManagerExtended $notificationManagerExtended)
    {
        parent::__construct($mailer, $router, $templating, $translator, $type);
        $this->notificationManager = $notificationManager;
        $this->notificationManagerExtended = $notificationManagerExtended;
    }


    public function notify(GarageMemberManagementEvent $event)
    {
        $this->checkEventClass($event, PendingRequestToJoinGarageCreatedEvent::class);

        $garage = $event->getGarageProUser()->getGarage();
        $proUser = $event->getGarageProUser()->getProUser();

        //Creation of the notification to administrators
        try {
            $data = [
                'garage' => $garage->getId(),
                'proUser' => $proUser->getId()
            ];

            /** @var EventNotification $notification */
            $notification = $this->notificationManagerExtended->createNotification(
                get_class($event->getGarageProUser()),
                get_class($event),
                json_encode($data),
                $this->router->generate('front_garage_view', [
                    'id' => $garage->getId(),
                    '_fragment' => 'sellers'])
            );

            $this->notificationManager->addNotification($garage->getAdministrators(), $notification, true);
        } catch (OptimisticLockException $e) {
            // tant pis pour la notification, on ne bloque pas l'action
        }

        // Send an email to administrators
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
        }
    }

}