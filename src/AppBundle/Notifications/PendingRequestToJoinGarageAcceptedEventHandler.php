<?php

namespace AppBundle\Notifications;


use AppBundle\MailWorkflow\AbstractEmailEventHandler;
use AppBundle\MailWorkflow\Services\Mailer;
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

    /**
     * @inheritDoc
     */
    public function __construct(Mailer $mailer, UrlGeneratorInterface $router, EngineInterface $templating, TranslatorInterface $translator, string $type, NotificationManager $notificationsManager)
    {
        parent::__construct($mailer, $router, $templating, $translator, $type);
        $this->notificationsManager = $notificationsManager;
    }


    public function notify(GarageMemberManagementEvent $event)
    {
        $this->checkEventClass($event, PendingRequestToJoinGarageAcceptedEvent::class);
        /*
                $garage = $event->getGarageProUser()->getGarage();
                $proUser = $event->getGarageProUser()->getProUser();

                //Creation of the notification to administrators
                try {
                    $data = [
                        'garageId' => $garage->getId(),
                        'proUserId' => $proUser->getId(),
                        'reason' => get_class($event)
                    ];

                    $notification = $this->notificationsManager->createNotification(
                        get_class($event->getGarageProUser()),
                        json_encode($data),
                        $this->router->generate('front_garage_view', [
                            'id' => $garage->getId(),
                            '_fragment' => 'sellers'])
                    );

                    $this->notificationsManager->addNotification($garage->getAdministrators(), $notification, true);
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
                }*/
    }

}