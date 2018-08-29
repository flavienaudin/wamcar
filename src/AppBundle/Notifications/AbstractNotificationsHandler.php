<?php

namespace AppBundle\Notifications;


use Mgilet\NotificationBundle\Manager\NotificationManager;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Wamcar\User\Event\LikeVehicleEventHandler;

abstract class AbstractNotificationsHandler implements LikeVehicleEventHandler
{
    /** @var NotificationManager $notificationsManager */
    protected $notificationsManager;

    /** @var UrlGeneratorInterface $router */
    protected $router;

    /**
     * AbstractNotificationsHandler constructor.
     * @param NotificationManager $notificationsManager
     * @param UrlGeneratorInterface $router
     */
    public function __construct(NotificationManager $notificationsManager, UrlGeneratorInterface $router)
    {
        $this->notificationsManager = $notificationsManager;
        $this->router = $router;
    }


    protected function checkEventClass($event, $className)
    {
        if (!$event instanceof $className) {
            throw new \InvalidArgumentException(static::class . " can only be notified of '$className' events");
        }
    }
}