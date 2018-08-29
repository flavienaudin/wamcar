<?php

namespace AppBundle\Notifications;


use Doctrine\ORM\OptimisticLockException;
use Wamcar\User\Event\LikeVehicleEvent;
use Wamcar\User\Event\UserLikeVehicleEvent;
use Wamcar\Vehicle\ProVehicle;

class LikeNotificationsHandler extends AbstractNotificationsHandler
{
    /**
     * @inheritDoc
     */
    public function notify(LikeVehicleEvent $event)
    {
        $this->checkEventClass($event, UserLikeVehicleEvent::class);

        $userLiking = $event->getLikeVehicle()->getUser();
        $vehicle = $event->getLikeVehicle()->getVehicle();

        if($event->getLikeVehicle()->getValue()) {
            // TODO : générer le message plus paramétrable quand il sera possible d'étendre la classe Notification
            $notification = $this->notificationsManager->createNotification(
                $userLiking->getFullName() . " aime votre annonce " . $vehicle->getName(),
                null,
                $this->router->generate($vehicle instanceof ProVehicle ? 'front_vehicle_pro_detail' : 'front_vehicle_personal_detail', ['id' => $vehicle->getId(), '_fragment' => 'js-interested_users'])
            );
            try {
                $this->notificationsManager->addNotification([$vehicle->getSeller()], $notification, true);
            }catch (OptimisticLockException $e) {
                // tant pis pour la notification, on ne bloque pas l'action
            }
        }
    }
}