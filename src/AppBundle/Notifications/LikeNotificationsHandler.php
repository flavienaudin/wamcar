<?php

namespace AppBundle\Notifications;


use AppBundle\MailWorkflow\AbstractEmailEventHandler;
use AppBundle\MailWorkflow\Model\EmailRecipientList;
use AppBundle\MailWorkflow\Services\Mailer;
use AppBundle\Services\Notification\NotificationManagerExtended;
use AppBundle\Services\Picture\PathVehiclePicture;
use Doctrine\ORM\OptimisticLockException;
use Mgilet\NotificationBundle\Manager\NotificationManager;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Wamcar\User\Event\LikeVehicleEvent;
use Wamcar\User\Event\LikeVehicleEventHandler;
use Wamcar\User\Event\UserLikeVehicleEvent;
use Wamcar\Vehicle\Enum\NotificationFrequency;
use Wamcar\Vehicle\ProVehicle;

class LikeNotificationsHandler extends AbstractEmailEventHandler implements LikeVehicleEventHandler
{

    /** @var NotificationManager $notificationsManager */
    protected $notificationsManager;
    /** @var NotificationManagerExtended $notificationsManagerExtended */
    protected $notificationsManagerExtended;
    /** @var PathVehiclePicture $pathVehiclePicture */
    private $pathVehiclePicture;

    /**
     * LikeNotificationsHandler constructor.
     * @param Mailer $mailer
     * @param UrlGeneratorInterface $router
     * @param EngineInterface $templating
     * @param TranslatorInterface $translator
     * @param string $type
     * @param NotificationManager $notificationsManager
     * @param NotificationManagerExtended $notificationsManagerExtended
     * @param PathVehiclePicture $pathVehiclePicture
     */
    public function __construct(Mailer $mailer, UrlGeneratorInterface $router, EngineInterface $templating, TranslatorInterface $translator, string $type, NotificationManager $notificationsManager, NotificationManagerExtended $notificationsManagerExtended, PathVehiclePicture $pathVehiclePicture)
    {
        parent::__construct($mailer, $router, $templating, $translator, $type);
        $this->notificationsManager = $notificationsManager;
        $this->notificationsManagerExtended = $notificationsManagerExtended;
        $this->pathVehiclePicture = $pathVehiclePicture;
    }

    /**
     * @inheritDoc
     */
    public function notify(LikeVehicleEvent $event)
    {
        $this->checkEventClass($event, UserLikeVehicleEvent::class);

        $like = $event->getLikeVehicle();
        $data = json_encode(['id' => $like->getId()]);

        $vehicle = $event->getLikeVehicle()->getVehicle();

        if (!$like->getUser()->is($like->getVehicle()->getSeller())) {
            // If user likes its own vehicle Then no notification
            if ($event->getLikeVehicle()->getValue()) {
                // Event is "like" : create notification
                $notifications = $this->notificationsManagerExtended->createNotification(
                    get_class($like),
                    get_class($event),
                    $data,
                    $vehicle instanceof ProVehicle ?
                        $this->router->generate('front_vehicle_pro_detail', ['slug' => $vehicle->getSlug(), '_fragment' => 'js-interested_users'])
                        : $this->router->generate('front_vehicle_personal_detail', ['slug' => $vehicle->getSlug(), '_fragment' => 'js-interested_users'])
                );
                try {
                    $this->notificationsManager->addNotification([$vehicle->getSeller()], $notifications, true);
                } catch (OptimisticLockException $e) {
                    // tant pis pour la notification, on ne bloque pas l'action
                }

                if ($like->getVehicle()->getSeller()->getPreferences()->isLikeEmailEnabled() &&
                    $like->getVehicle()->getSeller()->getPreferences()->getLikeEmailFrequency()->getValue() === NotificationFrequency::IMMEDIATELY) {

                    $pathImg = $this->pathVehiclePicture->getPath($like->getVehicle()->getMainPicture(), 'vehicle_mini_thumbnail');

                    $this->send(
                        $this->translator->trans('notifyUserOfLikeVehicle.object', ['%annonceTitle%' => $like->getVehicle()->getName()], 'email'),
                        'Mail/notifyUserOfNewLikeVehicle.html.twig',
                        [
                            'username' => $like->getVehicle()->getSellerName(true),
                            'messageAuthorName' => $like->getUser()->getFullName(),
                            'annonceTitle' => $like->getVehicle()->getName(),
                            'message_url' =>
                                $like->getVehicle() instanceof ProVehicle ?
                                    $this->router->generate("front_vehicle_pro_detail", ['slug' => $like->getVehicle()->getSlug(), '_fragment' => 'js-interested_users'], UrlGeneratorInterface::ABSOLUTE_URL)
                                    : $this->router->generate("front_vehicle_personal_detail", ['slug' => $like->getVehicle()->getSlug(), '_fragment' => 'js-interested_users'], UrlGeneratorInterface::ABSOLUTE_URL)
                            ,
                            'vehicle' => $like->getVehicle(),
                            'vehicleUrl' => $like->getVehicle() instanceof ProVehicle ?
                                $this->router->generate("front_vehicle_pro_detail", ['slug' => $like->getVehicle()->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL)
                                : $this->router->generate("front_vehicle_personal_detail", ['slug' => $like->getVehicle()->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL),
                            'vehiclePrice' => ($like->getVehicle() instanceof ProVehicle ? $like->getVehicle()->getPrice() : null),
                            'thumbnailUrl' => $pathImg
                        ],
                        new EmailRecipientList($this->createUserEmailContact($like->getVehicle()->getSeller()))
                    );
                }
            } else {
                // Event is "unlike" : remove notification about the like
                $notifications = $this->notificationsManagerExtended->getNotificationByObjectDescription([
                    'subject' => get_class($like),
                    'message' => $data
                ]);
                try {
                    foreach ($notifications as $notification) {
                        $this->notificationsManager->removeNotification([$vehicle->getSeller()], $notification);
                        $this->notificationsManager->deleteNotification($notification, true);
                    }
                } catch (OptimisticLockException $e) {
                    // tant pis pour la suppression des notifications, on ne bloque pas l'action
                }
            }
        }
    }
}