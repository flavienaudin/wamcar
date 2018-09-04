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

        $like = $event->getLikeVehicle();
        $data = json_encode([
            'identifier' => $like->getId()
        ]);

        $vehicle = $event->getLikeVehicle()->getVehicle();

        if($event->getLikeVehicle()->getValue()) {
            $notification = $this->notificationsManager->createNotification(
                get_class($like) ,
                $data,
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