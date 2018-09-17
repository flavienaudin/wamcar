<?php

namespace AppBundle\Notifications;


use AppBundle\MailWorkflow\AbstractEmailEventHandler;
use AppBundle\MailWorkflow\Model\EmailRecipientList;
use AppBundle\MailWorkflow\Services\Mailer;
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
    /** @var PathVehiclePicture $pathVehiclePicture */
    private $pathVehiclePicture;

    /**
     * @inheritDoc
     */
    public function __construct(Mailer $mailer, UrlGeneratorInterface $router, EngineInterface $templating, TranslatorInterface $translator, string $type, NotificationManager $notificationsManager, PathVehiclePicture $pathVehiclePicture)
    {
        parent::__construct($mailer, $router, $templating, $translator, $type);
        $this->notificationsManager = $notificationsManager;
        $this->pathVehiclePicture = $pathVehiclePicture;
    }

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

        if ($event->getLikeVehicle()->getValue()) {
            $notification = $this->notificationsManager->createNotification(
                get_class($like),
                $data,
                $this->router->generate($vehicle instanceof ProVehicle ? 'front_vehicle_pro_detail' : 'front_vehicle_personal_detail', ['id' => $vehicle->getId(), '_fragment' => 'js-interested_users'])
            );
            try {
                $this->notificationsManager->addNotification([$vehicle->getSeller()], $notification, true);
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
                        'messageAuthorName' => $like->getUser()->getFullName(),
                        'annonceTitle' => $like->getVehicle()->getName(),
                        'message_url' =>
                            $like->getVehicle() instanceof ProVehicle ?
                                $this->router->generate("front_vehicle_pro_detail", ['id' => $like->getVehicle()->getId(), '_fragment' => 'js-interested_users'], UrlGeneratorInterface::ABSOLUTE_URL)
                                :
                                $this->router->generate("front_vehicle_personal_detail", ['id' => $like->getVehicle()->getId(), '_fragment' => 'js-interested_users'], UrlGeneratorInterface::ABSOLUTE_URL)
                        ,
                        'vehicle' => $like->getVehicle(),
                        'vehiclePrice' => ($like->getVehicle() instanceof ProVehicle ? $like->getVehicle()->getPrice() : null),
                        'thumbnailUrl' => $pathImg
                    ],
                    new EmailRecipientList($this->createUserEmailContact($like->getVehicle()->getSeller()))
                );
            }

        }
    }
}