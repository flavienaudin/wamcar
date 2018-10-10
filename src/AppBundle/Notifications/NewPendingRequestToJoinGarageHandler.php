<?php

namespace AppBundle\Notifications;


use AppBundle\MailWorkflow\AbstractEmailEventHandler;
use AppBundle\MailWorkflow\Services\Mailer;
use Doctrine\ORM\OptimisticLockException;
use Mgilet\NotificationBundle\Manager\NotificationManager;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Wamcar\Garage\Event\NewPendingRequestToJoinGarage;
use Wamcar\Garage\Event\PendingRequestToJoinGarageEvent;
use Wamcar\Garage\Event\PendingRequestToJoinGarageEventHandler;

class NewPendingRequestToJoinGarageHandler extends AbstractEmailEventHandler implements PendingRequestToJoinGarageEventHandler
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


    public function notify(PendingRequestToJoinGarageEvent $event)
    {
        $this->checkEventClass($event, NewPendingRequestToJoinGarage::class);

        $garage = $event->getGarageProUser()->getGarage();

        //Creation of the notification to administrators
        try {
            $data = json_encode([
                'garageId' => $garage->getId(),
                'proUserId' => $event->getGarageProUser()->getProUser()->getId()
            ]);
            $notification = $this->notificationsManager->createNotification(
                get_class($event->getGarageProUser()),
                $data,
                $this->router->generate('front_garage_view', [
                    'id' => $garage->getId(),
                    '_fragment' => 'sellers'])
            );

            $this->notificationsManager->addNotification($garage->getAdministrators(), $notification, true);
        } catch (OptimisticLockException $e) {
            // tant pis pour la notification, on ne bloque pas l'action
        }

        // Send an email to administrators
        // TODO
    }
}